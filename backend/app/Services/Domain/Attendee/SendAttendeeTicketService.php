<?php

namespace Evently\Services\Domain\Attendee;

use Evently\DomainObjects\AttendeeDomainObject;
use Evently\DomainObjects\EventDomainObject;
use Evently\DomainObjects\EventSettingDomainObject;
use Evently\DomainObjects\OrderDomainObject;
use Evently\DomainObjects\OrganizerDomainObject;
use Evently\Mail\Attendee\AttendeeTicketMail;
use Illuminate\Contracts\Mail\Mailer;

class SendAttendeeTicketService
{
    public function __construct(
        private readonly Mailer $mailer
    )
    {
    }

    public function send(
        OrderDomainObject        $order,
        AttendeeDomainObject     $attendee,
        EventDomainObject        $event,
        EventSettingDomainObject $eventSettings,
        OrganizerDomainObject    $organizer,
    ): void
    {
        $this->mailer
            ->to($attendee->getEmail())
            ->locale($attendee->getLocale())
            ->send(new AttendeeTicketMail(
                order: $order,
                attendee: $attendee,
                event: $event,
                eventSettings: $eventSettings,
                organizer: $organizer,
            ));
    }
}
