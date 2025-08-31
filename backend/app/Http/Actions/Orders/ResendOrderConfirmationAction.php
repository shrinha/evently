<?php

namespace Evently\Http\Actions\Orders;

use Evently\DomainObjects\EventDomainObject;
use Evently\DomainObjects\EventSettingDomainObject;
use Evently\DomainObjects\Generated\OrderDomainObjectAbstract;
use Evently\DomainObjects\InvoiceDomainObject;
use Evently\DomainObjects\OrderItemDomainObject;
use Evently\DomainObjects\OrganizerDomainObject;
use Evently\Http\Actions\BaseAction;
use Evently\Mail\Order\OrderSummary;
use Evently\Repository\Eloquent\Value\Relationship;
use Evently\Repository\Interfaces\EventRepositoryInterface;
use Evently\Repository\Interfaces\OrderRepositoryInterface;
use Illuminate\Http\Response;
use Illuminate\Mail\Mailer;

class ResendOrderConfirmationAction extends BaseAction
{
    public function __construct(
        private readonly EventRepositoryInterface $eventRepository,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly Mailer                   $mailer,
    )
    {
    }

    /**
     * @todo - move this to a handler
     */
    public function __invoke(int $eventId, int $orderId): Response
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        $order = $this->orderRepository
            ->loadRelation(OrderItemDomainObject::class)
            ->loadRelation(InvoiceDomainObject::class)
            ->findFirstWhere([
                OrderDomainObjectAbstract::EVENT_ID => $eventId,
                OrderDomainObjectAbstract::ID => $orderId,
            ]);

        if (!$order) {
            return $this->notFoundResponse();
        }

        if ($order->isOrderCompleted()) {
            $event = $this->eventRepository
                ->loadRelation(new Relationship(OrganizerDomainObject::class, name: 'organizer'))
                ->loadRelation(new Relationship(EventSettingDomainObject::class))
                ->findById($order->getEventId());

            $this->mailer
                ->to($order->getEmail())
                ->locale($order->getLocale())
                ->send(new OrderSummary(
                    order: $order,
                    event: $event,
                    organizer: $event->getOrganizer(),
                    eventSettings: $event->getEventSettings(),
                    invoice: $order->getLatestInvoice(),
                ));
        }

        return $this->noContentResponse();
    }
}
