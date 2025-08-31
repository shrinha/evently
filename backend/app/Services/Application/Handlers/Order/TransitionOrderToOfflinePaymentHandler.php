<?php

namespace Evently\Services\Application\Handlers\Order;

use Evently\DomainObjects\Enums\PaymentProviders;
use Evently\DomainObjects\EventSettingDomainObject;
use Evently\DomainObjects\Generated\OrderDomainObjectAbstract;
use Evently\DomainObjects\OrderDomainObject;
use Evently\DomainObjects\OrderItemDomainObject;
use Evently\DomainObjects\Status\OrderPaymentStatus;
use Evently\DomainObjects\Status\OrderStatus;
use Evently\Events\OrderStatusChangedEvent;
use Evently\Exceptions\ResourceConflictException;
use Evently\Exceptions\UnauthorizedException;
use Evently\Repository\Interfaces\EventSettingsRepositoryInterface;
use Evently\Repository\Interfaces\OrderRepositoryInterface;
use Evently\Services\Application\Handlers\Order\DTO\TransitionOrderToOfflinePaymentPublicDTO;
use Evently\Services\Domain\Product\ProductQuantityUpdateService;
use Evently\Services\Infrastructure\DomainEvents\DomainEventDispatcherService;
use Evently\Services\Infrastructure\DomainEvents\Enums\DomainEventType;
use Evently\Services\Infrastructure\DomainEvents\Events\OrderEvent;
use Illuminate\Database\DatabaseManager;

class TransitionOrderToOfflinePaymentHandler
{
    public function __construct(
        private readonly ProductQuantityUpdateService     $productQuantityUpdateService,
        private readonly OrderRepositoryInterface         $orderRepository,
        private readonly DatabaseManager                  $databaseManager,
        private readonly EventSettingsRepositoryInterface $eventSettingsRepository,
        private readonly DomainEventDispatcherService     $domainEventDispatcherService,

    )
    {
    }

    public function handle(TransitionOrderToOfflinePaymentPublicDTO $dto): OrderDomainObject
    {
        return $this->databaseManager->transaction(function () use ($dto) {
            /** @var OrderDomainObjectAbstract $order */
            $order = $this->orderRepository
                ->loadRelation(OrderItemDomainObject::class)
                ->findByShortId($dto->orderShortId);

            /** @var EventSettingDomainObject $eventSettings */
            $eventSettings = $this->eventSettingsRepository->findFirstWhere([
                'event_id' => $order->getEventId(),
            ]);

            $this->validateOfflinePayment($order, $eventSettings);

            $this->updateOrderStatuses($order->getId());

            $this->productQuantityUpdateService->updateQuantitiesFromOrder($order);

            $order = $this->orderRepository
                ->loadRelation(OrderItemDomainObject::class)
                ->findById($order->getId());

            event(new OrderStatusChangedEvent(
                order: $order,
                sendEmails: true,
                createInvoice: $eventSettings->getEnableInvoicing(),
            ));

            $this->domainEventDispatcherService->dispatch(
                new OrderEvent(
                    type: DomainEventType::ORDER_CREATED,
                    orderId: $order->getId(),
                ),
            );

            return $order;
        });
    }

    private function updateOrderStatuses(int $orderId): void
    {
        $this->orderRepository
            ->updateFromArray($orderId, [
                OrderDomainObjectAbstract::PAYMENT_STATUS => OrderPaymentStatus::AWAITING_OFFLINE_PAYMENT->name,
                OrderDomainObjectAbstract::STATUS => OrderStatus::AWAITING_OFFLINE_PAYMENT->name,
                OrderDomainObjectAbstract::PAYMENT_PROVIDER => PaymentProviders::OFFLINE->value,
            ]);
    }

    /**
     * @throws ResourceConflictException
     */
    public function validateOfflinePayment(
        OrderDomainObject        $order,
        EventSettingDomainObject $settings,
    ): void
    {
        if (!$order->isOrderReserved()) {
            throw new ResourceConflictException(__('Order is not in the correct status to transition to offline payment'));
        }

        if ($order->isReservedOrderExpired()) {
            throw new ResourceConflictException(__('Order reservation has expired'));
        }

        if (collect($settings->getPaymentProviders())->contains(PaymentProviders::OFFLINE->value) === false) {
            throw new UnauthorizedException(__('Offline payments are not enabled for this event'));
        }
    }
}
