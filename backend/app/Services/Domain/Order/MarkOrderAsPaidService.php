<?php

namespace Evently\Services\Domain\Order;

use Brick\Math\Exception\MathException;
use Evently\DomainObjects\AccountConfigurationDomainObject;
use Evently\DomainObjects\AccountDomainObject;
use Evently\DomainObjects\AttendeeDomainObject;
use Evently\DomainObjects\Enums\PaymentProviders;
use Evently\DomainObjects\EventDomainObject;
use Evently\DomainObjects\EventSettingDomainObject;
use Evently\DomainObjects\Generated\OrderDomainObjectAbstract;
use Evently\DomainObjects\InvoiceDomainObject;
use Evently\DomainObjects\OrderDomainObject;
use Evently\DomainObjects\OrderItemDomainObject;
use Evently\DomainObjects\OrganizerDomainObject;
use Evently\DomainObjects\Status\AttendeeStatus;
use Evently\DomainObjects\Status\InvoiceStatus;
use Evently\DomainObjects\Status\OrderApplicationFeeStatus;
use Evently\DomainObjects\Status\OrderPaymentStatus;
use Evently\DomainObjects\Status\OrderStatus;
use Evently\Events\OrderStatusChangedEvent;
use Evently\Exceptions\ResourceConflictException;
use Evently\Repository\Eloquent\Value\Relationship;
use Evently\Repository\Interfaces\AffiliateRepositoryInterface;
use Evently\Repository\Interfaces\AttendeeRepositoryInterface;
use Evently\Repository\Interfaces\EventRepositoryInterface;
use Evently\Repository\Interfaces\InvoiceRepositoryInterface;
use Evently\Repository\Interfaces\OrderRepositoryInterface;
use Evently\Services\Domain\Mail\SendOrderDetailsService;
use Evently\Services\Infrastructure\DomainEvents\DomainEventDispatcherService;
use Evently\Services\Infrastructure\DomainEvents\Enums\DomainEventType;
use Evently\Services\Infrastructure\DomainEvents\Events\OrderEvent;
use Illuminate\Database\DatabaseManager;
use Throwable;

class MarkOrderAsPaidService
{
    public function __construct(
        private readonly OrderRepositoryInterface              $orderRepository,
        private readonly DatabaseManager                       $databaseManager,
        private readonly AffiliateRepositoryInterface          $affiliateRepository,
        private readonly InvoiceRepositoryInterface            $invoiceRepository,
        private readonly AttendeeRepositoryInterface           $attendeeRepository,
        private readonly DomainEventDispatcherService          $domainEventDispatcherService,
        private readonly OrderApplicationFeeCalculationService $orderApplicationFeeCalculationService,
        private readonly EventRepositoryInterface              $eventRepository,
        private readonly OrderApplicationFeeService            $orderApplicationFeeService,
        private readonly SendOrderDetailsService               $sendOrderDetailsService,
    )
    {
    }

    /**
     * @throws ResourceConflictException|Throwable
     */
    public function markOrderAsPaid(
        int $orderId,
        int $eventId,
    ): OrderDomainObject
    {
        return $this->databaseManager->transaction(function () use ($orderId, $eventId) {
            /** @var OrderDomainObject $order */
            $order = $this->orderRepository
                ->loadRelation(OrderItemDomainObject::class)
                ->loadRelation(AttendeeDomainObject::class)
                ->loadRelation(InvoiceDomainObject::class)
                ->findFirstWhere([
                    OrderDomainObjectAbstract::ID => $orderId,
                    OrderDomainObjectAbstract::EVENT_ID => $eventId,
                ]);

            $event = $this->eventRepository
                ->loadRelation(new Relationship(OrganizerDomainObject::class, name: 'organizer'))
                ->loadRelation(new Relationship(EventSettingDomainObject::class))
                ->findById($order->getEventId());

            if ($order->getStatus() !== OrderStatus::AWAITING_OFFLINE_PAYMENT->name) {
                throw new ResourceConflictException(__('Order is not awaiting offline payment'));
            }

            $this->updateOrderStatus($orderId);

            $this->updateOrderInvoice($orderId);

            $updatedOrder = $this->orderRepository
                ->loadRelation(OrderItemDomainObject::class)
                ->findById($orderId);

            // Update affiliate sales if this order has an affiliate
            if ($updatedOrder->getAffiliateId()) {
                $this->affiliateRepository->incrementSales(
                    $updatedOrder->getAffiliateId(),
                    $updatedOrder->getTotalGross()
                );
            }

            $this->updateAttendeeStatuses($updatedOrder);

            event(new OrderStatusChangedEvent(
                order: $updatedOrder,
                sendEmails: false
            ));

            $this->domainEventDispatcherService->dispatch(
                new OrderEvent(
                    type: DomainEventType::ORDER_MARKED_AS_PAID,
                    orderId: $orderId,
                ),
            );

            $this->storeApplicationFeePayment($updatedOrder);

            $this->sendOrderDetailsService->sendCustomerOrderSummary(
                order: $updatedOrder,
                event: $event,
                organizer: $event->getOrganizer(),
                eventSettings: $event->getEventSettings(),
                invoice: $order->getLatestInvoice(),
            );

            return $updatedOrder;
        });
    }

    private function updateOrderInvoice(int $orderId): void
    {
        $invoice = $this->invoiceRepository->findLatestInvoiceForOrder($orderId);

        if ($invoice) {
            $this->invoiceRepository->updateFromArray($invoice->getId(), [
                'status' => InvoiceStatus::PAID->name,
            ]);
        }
    }

    private function updateOrderStatus(int $orderId): void
    {
        $this->orderRepository->updateFromArray($orderId, [
            OrderDomainObjectAbstract::STATUS => OrderStatus::COMPLETED->name,
            OrderDomainObjectAbstract::PAYMENT_STATUS => OrderPaymentStatus::PAYMENT_RECEIVED->name,
        ]);
    }

    private function updateAttendeeStatuses(OrderDomainObject $updatedOrder): void
    {
        $this->attendeeRepository->updateWhere(
            attributes: [
                'status' => AttendeeStatus::ACTIVE->name,
            ],
            where: [
                'order_id' => $updatedOrder->getId(),
                'status' => AttendeeStatus::AWAITING_PAYMENT->name,
            ],
        );
    }

    /**
     * @throws MathException
     */
    private function storeApplicationFeePayment(OrderDomainObject $updatedOrder): void
    {
        /** @var EventDomainObject $event */
        $event = $this->eventRepository
            ->loadRelation(new Relationship(
                domainObject: AccountDomainObject::class,
                nested: [
                    new Relationship(
                        domainObject: AccountConfigurationDomainObject::class,
                        name: 'configuration',
                    ),
                ],
                name: 'account'
            ))
            ->findById($updatedOrder->getEventId());

        /** @var AccountConfigurationDomainObject $config */
        $config = $event->getAccount()->getConfiguration();

        $this->orderApplicationFeeService->createOrderApplicationFee(
            orderId: $updatedOrder->getId(),
            applicationFeeAmountMinorUnit: $this->orderApplicationFeeCalculationService->calculateApplicationFee(
                accountConfiguration: $config,
                order: $updatedOrder,
            )->toMinorUnit(),
            orderApplicationFeeStatus: OrderApplicationFeeStatus::AWAITING_PAYMENT,
            paymentMethod: PaymentProviders::OFFLINE,
            currency: $updatedOrder->getCurrency(),
        );
    }
}
