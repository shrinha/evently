<?php

namespace Evently\Services\Application\Handlers\Order\Payment\Stripe;

use Brick\Math\Exception\MathException;
use Brick\Math\Exception\NumberFormatException;
use Brick\Math\Exception\RoundingNecessaryException;
use Brick\Money\Exception\UnknownCurrencyException;
use Evently\DomainObjects\EventDomainObject;
use Evently\DomainObjects\EventSettingDomainObject;
use Evently\DomainObjects\Generated\OrderDomainObjectAbstract;
use Evently\DomainObjects\OrderDomainObject;
use Evently\DomainObjects\OrganizerDomainObject;
use Evently\DomainObjects\Status\OrderRefundStatus;
use Evently\DomainObjects\StripePaymentDomainObject;
use Evently\Exceptions\RefundNotPossibleException;
use Evently\Mail\Order\OrderRefunded;
use Evently\Repository\Eloquent\Value\Relationship;
use Evently\Repository\Interfaces\EventRepositoryInterface;
use Evently\Repository\Interfaces\OrderRepositoryInterface;
use Evently\Services\Application\Handlers\Order\DTO\RefundOrderDTO;
use Evently\Services\Domain\Order\OrderCancelService;
use Evently\Services\Domain\Payment\Stripe\StripePaymentIntentRefundService;
use Evently\Values\MoneyValue;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Database\DatabaseManager;
use Stripe\Exception\ApiErrorException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Throwable;

readonly class RefundOrderHandler
{
    public function __construct(
        private StripePaymentIntentRefundService $refundService,
        private OrderRepositoryInterface         $orderRepository,
        private EventRepositoryInterface         $eventRepository,
        private Mailer                           $mailer,
        private OrderCancelService               $orderCancelService,
        private DatabaseManager                  $databaseManager,
    )
    {
    }

    /**
     * @throws RefundNotPossibleException
     * @throws ApiErrorException
     * @throws Throwable
     */
    public function handle(RefundOrderDTO $refundOrderDTO): OrderDomainObject
    {
        return $this->databaseManager->transaction(fn() => $this->refundOrder($refundOrderDTO));
    }

    private function fetchOrder(int $eventId, int $orderId): OrderDomainObject
    {
        $order = $this->orderRepository
            ->loadRelation(new Relationship(StripePaymentDomainObject::class, name: 'stripe_payment'))
            ->findFirstWhere(['event_id' => $eventId, 'id' => $orderId]);

        if (!$order) {
            throw new ResourceNotFoundException();
        }

        return $order;
    }

    /**
     * @throws RefundNotPossibleException
     */
    private function validateRefundability(OrderDomainObject $order): void
    {
        if (!$order->getStripePayment()) {
            throw new RefundNotPossibleException(__('There is no Stripe data associated with this order.'));
        }

        if ($order->getRefundStatus() === OrderRefundStatus::REFUND_PENDING->name) {
            throw new RefundNotPossibleException(
                __('There is already a refund pending for this order.
                Please wait for the refund to be processed before requesting another one.')
            );
        }
    }

    private function notifyBuyer(OrderDomainObject $order, EventDomainObject $event, MoneyValue $amount): void
    {
        $this->mailer
            ->to($order->getEmail())
            ->locale($order->getLocale())
            ->send(new OrderRefunded(
                order: $order,
                event: $event,
                organizer: $event->getOrganizer(),
                eventSettings: $event->getEventSettings(),
                refundAmount: $amount
            ));
    }

    private function markOrderRefundPending(OrderDomainObject $order): OrderDomainObject
    {
        return $this->orderRepository->updateFromArray(
            id: $order->getId(),
            attributes: [
                OrderDomainObjectAbstract::REFUND_STATUS => OrderRefundStatus::REFUND_PENDING->name,
            ]
        );
    }

    /**
     * @throws ApiErrorException
     * @throws UnknownCurrencyException
     * @throws RefundNotPossibleException
     * @throws Throwable
     * @throws RoundingNecessaryException
     * @throws MathException
     * @throws NumberFormatException
     */
    private function refundOrder(RefundOrderDTO $refundOrderDTO): OrderDomainObject
    {
        $order = $this->fetchOrder($refundOrderDTO->event_id, $refundOrderDTO->order_id);
        $event = $this->eventRepository
            ->loadRelation(new Relationship(OrganizerDomainObject::class, name: 'organizer'))
            ->loadRelation(EventSettingDomainObject::class)
            ->findById($refundOrderDTO->event_id);

        $amount = MoneyValue::fromFloat($refundOrderDTO->amount, $order->getCurrency());

        $this->validateRefundability($order);

        if ($refundOrderDTO->cancel_order) {
            $this->orderCancelService->cancelOrder($order);
        }

        $this->refundService->refundPayment(
            amount: $amount,
            payment: $order->getStripePayment()
        );

        if ($refundOrderDTO->notify_buyer) {
            $this->notifyBuyer($order, $event, $amount);
        }

        return $this->markOrderRefundPending($order);
    }
}
