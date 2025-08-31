<?php

namespace Evently\Services\Domain\Payment\Stripe\EventHandlers;

use Evently\DomainObjects\Generated\OrderDomainObjectAbstract;
use Evently\DomainObjects\Generated\StripePaymentDomainObjectAbstract;
use Evently\DomainObjects\OrderDomainObject;
use Evently\DomainObjects\OrderItemDomainObject;
use Evently\DomainObjects\Status\OrderPaymentStatus;
use Evently\Events\OrderStatusChangedEvent;
use Evently\Repository\Eloquent\StripePaymentsRepository;
use Evently\Repository\Eloquent\Value\Relationship;
use Evently\Repository\Interfaces\OrderRepositoryInterface;
use Evently\Services\Domain\Payment\Stripe\StripePaymentUpdateFromPaymentIntentService;
use Illuminate\Database\DatabaseManager;
use Stripe\PaymentIntent;
use Throwable;

readonly class PaymentIntentFailedHandler
{
    public function __construct(
        private OrderRepositoryInterface                    $orderRepository,
        private StripePaymentsRepository                    $stripePaymentsRepository,
        private DatabaseManager                             $databaseManager,
        private StripePaymentUpdateFromPaymentIntentService $stripePaymentUpdateFromPaymentIntentService,
    )
    {
    }

    /**
     * @throws Throwable
     */
    public function handleEvent(PaymentIntent $paymentIntent): void
    {
        $this->databaseManager->transaction(function () use ($paymentIntent) {
            /** @var StripePaymentDomainObjectAbstract $stripePayment */
            $stripePayment = $this->stripePaymentsRepository
                ->loadRelation(new Relationship(OrderDomainObject::class, name: 'order'))
                ->findFirstWhere([
                    StripePaymentDomainObjectAbstract::PAYMENT_INTENT_ID => $paymentIntent->id,
                ]);

            $this->stripePaymentUpdateFromPaymentIntentService->updateStripePaymentInfo($paymentIntent, $stripePayment);

            $updatedOrder = $this->updateOrderStatuses($stripePayment);

            OrderStatusChangedEvent::dispatch($updatedOrder);
        });
    }

    private function updateOrderStatuses(StripePaymentDomainObjectAbstract $stripePayment): OrderDomainObject
    {
        return $this->orderRepository
            ->loadRelation(OrderItemDomainObject::class)
            ->updateFromArray($stripePayment->getOrderId(), [
                OrderDomainObjectAbstract::PAYMENT_STATUS => OrderPaymentStatus::PAYMENT_FAILED->name,
            ]);
    }
}
