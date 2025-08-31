<?php

namespace Evently\Services\Domain\Order;

use Evently\DomainObjects\AttendeeDomainObject;
use Evently\DomainObjects\EventSettingDomainObject;
use Evently\DomainObjects\OrderDomainObject;
use Evently\DomainObjects\OrganizerDomainObject;
use Evently\DomainObjects\Status\AttendeeStatus;
use Evently\DomainObjects\Status\OrderStatus;
use Evently\Mail\Order\OrderCancelled;
use Evently\Repository\Eloquent\Value\Relationship;
use Evently\Repository\Interfaces\AttendeeRepositoryInterface;
use Evently\Repository\Interfaces\EventRepositoryInterface;
use Evently\Repository\Interfaces\OrderRepositoryInterface;
use Evently\Services\Domain\Product\ProductQuantityUpdateService;
use Evently\Services\Infrastructure\DomainEvents\DomainEventDispatcherService;
use Evently\Services\Infrastructure\DomainEvents\Enums\DomainEventType;
use Evently\Services\Infrastructure\DomainEvents\Events\OrderEvent;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Database\DatabaseManager;
use Throwable;

class OrderCancelService
{
    public function __construct(
        private readonly Mailer                       $mailer,
        private readonly AttendeeRepositoryInterface  $attendeeRepository,
        private readonly EventRepositoryInterface     $eventRepository,
        private readonly OrderRepositoryInterface     $orderRepository,
        private readonly DatabaseManager              $databaseManager,
        private readonly ProductQuantityUpdateService $productQuantityService,
        private readonly DomainEventDispatcherService $domainEventDispatcherService,
    )
    {
    }

    /**
     * @throws Throwable
     */
    public function cancelOrder(OrderDomainObject $order): void
    {
        $this->databaseManager->transaction(function () use ($order) {
            $this->adjustProductQuantities($order);
            $this->cancelAttendees($order);
            $this->updateOrderStatus($order);

            $event = $this->eventRepository
                ->loadRelation(new Relationship(OrganizerDomainObject::class, name: 'organizer'))
                ->loadRelation(EventSettingDomainObject::class)
                ->findById($order->getEventId());

            $this->mailer
                ->to($order->getEmail())
                ->locale($order->getLocale())
                ->send(new OrderCancelled(
                    order: $order,
                    event: $event,
                    organizer: $event->getOrganizer(),
                    eventSettings: $event->getEventSettings(),
                ));

            $this->domainEventDispatcherService->dispatch(
                new OrderEvent(
                    type: DomainEventType::ORDER_CANCELLED,
                    orderId: $order->getId(),
                ),
            );
        });
    }

    private function cancelAttendees(OrderDomainObject $order): void
    {
        $this->attendeeRepository->updateWhere(
            attributes: [
                'status' => AttendeeStatus::CANCELLED->name,
            ],
            where: [
                'order_id' => $order->getId(),
            ]
        );
    }

    private function adjustProductQuantities(OrderDomainObject $order): void
    {
        $attendees = $this->attendeeRepository->findWhere([
            'order_id' => $order->getId(),
        ])->filter(function (AttendeeDomainObject $attendee) use ($order) {
            if ($order->isOrderAwaitingOfflinePayment()) {
                return $attendee->getStatus() === AttendeeStatus::ACTIVE->name
                    || $attendee->getStatus() === AttendeeStatus::AWAITING_PAYMENT->name;
            }

            return $attendee->getStatus() === AttendeeStatus::ACTIVE->name;
        });

        $productIdCountMap = $attendees
            ->map(fn(AttendeeDomainObject $attendee) => $attendee->getProductPriceId())->countBy();

        foreach ($productIdCountMap as $productPriceId => $count) {
            $this->productQuantityService->decreaseQuantitySold($productPriceId, $count);
        }
    }

    private function updateOrderStatus(OrderDomainObject $order): void
    {
        $this->orderRepository->updateWhere(
            attributes: [
                'status' => OrderStatus::CANCELLED->name,
            ],
            where: [
                'id' => $order->getId(),
            ]
        );
    }
}
