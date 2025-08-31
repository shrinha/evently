<?php

namespace Evently\Http\Actions\Attendees;

use Evently\DomainObjects\EventDomainObject;
use Evently\Http\Actions\BaseAction;
use Evently\Services\Application\Handlers\Attendee\DTO\ResendAttendeeTicketDTO;
use Evently\Services\Application\Handlers\Attendee\ResendAttendeeTicketHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class ResendAttendeeTicketAction extends BaseAction
{
    public function __construct(
        private readonly ResendAttendeeTicketHandler $handler
    )
    {
    }

    public function __invoke(int $eventId, int $attendeeId): JsonResponse|Response
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        try {
            $this->handler->handle(new ResendAttendeeTicketDTO(
                attendeeId: $attendeeId,
                eventId: $eventId
            ));

        } catch (ResourceNotFoundException $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_CONFLICT);
        }

        return $this->noContentResponse();
    }
}
