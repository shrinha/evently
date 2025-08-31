<?php

namespace Evently\Services\Application\Handlers\Attendee;

use Evently\DomainObjects\EventSettingDomainObject;
use Evently\DomainObjects\OrderDomainObject;
use Evently\DomainObjects\OrganizerDomainObject;
use Evently\DomainObjects\Status\AttendeeStatus;
use Evently\Exceptions\ResourceConflictException;
use Evently\Repository\Eloquent\Value\Relationship;
use Evently\Repository\Interfaces\AttendeeRepositoryInterface;
use Evently\Repository\Interfaces\EventRepositoryInterface;
use Evently\Services\Application\Handlers\Attendee\DTO\ResendAttendeeTicketDTO;
use Evently\Services\Domain\Attendee\SendAttendeeTicketService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

readonly class ResendAttendeeTicketHandler
{
    public function __construct(
        private SendAttendeeTicketService   $sendAttendeeProductService,
        private AttendeeRepositoryInterface $attendeeRepository,
        private EventRepositoryInterface    $eventRepository,
        private LoggerInterface             $logger,
    )
    {
    }

    /**
     * @throws ResourceConflictException
     */
    public function handle(ResendAttendeeTicketDTO $resendAttendeeProductDTO): void
    {
        $attendee = $this->attendeeRepository
            ->loadRelation(new Relationship(OrderDomainObject::class, name: 'order'))
            ->findFirstWhere([
                'id' => $resendAttendeeProductDTO->attendeeId,
                'event_id' => $resendAttendeeProductDTO->eventId,
            ]);

        if (!$attendee) {
            throw new ResourceNotFoundException();
        }

        if ($attendee->getStatus() !== AttendeeStatus::ACTIVE->name) {
            throw new ResourceConflictException('You cannot resend the ticket of an inactive attendee');
        }

        $event = $this->eventRepository
            ->loadRelation(new Relationship(OrganizerDomainObject::class, name: 'organizer'))
            ->loadRelation(EventSettingDomainObject::class)
            ->findById($resendAttendeeProductDTO->eventId);

        $this->sendAttendeeProductService->send(
            order: $attendee->getOrder(),
            attendee: $attendee,
            event: $event,
            eventSettings: $event->getEventSettings(),
            organizer: $event->getOrganizer(),
        );

        $this->logger->info('Attendee ticket resent', [
            'attendeeId' => $resendAttendeeProductDTO->attendeeId,
            'eventId' => $resendAttendeeProductDTO->eventId
        ]);
    }
}
