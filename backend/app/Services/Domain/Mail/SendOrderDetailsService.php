<?php

namespace Evently\Services\Domain\Mail;

use Evently\DomainObjects\AttendeeDomainObject;
use Evently\DomainObjects\EventDomainObject;
use Evently\DomainObjects\EventSettingDomainObject;
use Evently\DomainObjects\InvoiceDomainObject;
use Evently\DomainObjects\OrderDomainObject;
use Evently\DomainObjects\OrderItemDomainObject;
use Evently\DomainObjects\OrganizerDomainObject;
use Evently\Mail\Order\OrderFailed;
use Evently\Mail\Order\OrderSummary;
use Evently\Mail\Organizer\OrderSummaryForOrganizer;
use Evently\Repository\Eloquent\Value\Relationship;
use Evently\Repository\Interfaces\EventRepositoryInterface;
use Evently\Repository\Interfaces\OrderRepositoryInterface;
use Evently\Services\Domain\Attendee\SendAttendeeTicketService;
use Illuminate\Mail\Mailer;

class SendOrderDetailsService
{
    public function __construct(
        private readonly EventRepositoryInterface  $eventRepository,
        private readonly OrderRepositoryInterface  $orderRepository,
        private readonly Mailer                    $mailer,
        private readonly SendAttendeeTicketService $sendAttendeeTicketService,
    )
    {
    }

    public function sendOrderSummaryAndTicketEmails(OrderDomainObject $order): void
    {
        $order = $this->orderRepository
            ->loadRelation(OrderItemDomainObject::class)
            ->loadRelation(AttendeeDomainObject::class)
            ->loadRelation(InvoiceDomainObject::class)
            ->findById($order->getId());

        $event = $this->eventRepository
            ->loadRelation(new Relationship(OrganizerDomainObject::class, name: 'organizer'))
            ->loadRelation(new Relationship(EventSettingDomainObject::class))
            ->findById($order->getEventId());

        if ($order->isOrderCompleted() || $order->isOrderAwaitingOfflinePayment()) {
            $this->sendOrderSummaryEmails($order, $event);
            $this->sendAttendeeTicketEmails($order, $event);
        }

        if ($order->isOrderFailed()) {
            $this->mailer
                ->to($order->getEmail())
                ->locale($order->getLocale())
                ->send(new OrderFailed(
                    order: $order,
                    event: $event,
                    organizer: $event->getOrganizer(),
                    eventSettings: $event->getEventSettings(),
                ));
        }
    }

    public function sendCustomerOrderSummary(
        OrderDomainObject        $order,
        EventDomainObject        $event,
        OrganizerDomainObject    $organizer,
        EventSettingDomainObject $eventSettings,
        ?InvoiceDomainObject     $invoice = null
    ): void
    {
        $this->mailer
            ->to($order->getEmail())
            ->locale($order->getLocale())
            ->send(new OrderSummary(
                order: $order,
                event: $event,
                organizer: $organizer,
                eventSettings: $eventSettings,
                invoice: $invoice,
            ));
    }

    private function sendAttendeeTicketEmails(OrderDomainObject $order, EventDomainObject $event): void
    {
        $sentEmails = [];
        foreach ($order->getAttendees() as $attendee) {
            if (in_array($attendee->getEmail(), $sentEmails, true)) {
                continue;
            }

            $this->sendAttendeeTicketService->send(
                order: $order,
                attendee: $attendee,
                event: $event,
                eventSettings: $event->getEventSettings(),
                organizer: $event->getOrganizer(),
            );

            $sentEmails[] = $attendee->getEmail();
        }
    }

    private function sendOrderSummaryEmails(OrderDomainObject $order, EventDomainObject $event): void
    {
        $this->sendCustomerOrderSummary(
            order: $order,
            event: $event,
            organizer: $event->getOrganizer(),
            eventSettings: $event->getEventSettings(),
            invoice: $order->getLatestInvoice(),
        );

        if ($order->getIsManuallyCreated() || !$event->getEventSettings()->getNotifyOrganizerOfNewOrders()) {
            return;
        }

        $this->mailer
            ->to($event->getOrganizer()->getEmail())
            ->send(new OrderSummaryForOrganizer($order, $event));
    }
}
