<?php

namespace Evently\Http\Actions\Attendees;

use Evently\DomainObjects\EventDomainObject;
use Evently\Exceptions\CannotCheckInException;
use Evently\Http\Actions\BaseAction;
use Evently\Http\Request\Attendee\CheckInAttendeeRequest;
use Evently\Http\ResponseCodes;
use Evently\Resources\Attendee\AttendeeResource;
use Evently\Services\Application\Handlers\Attendee\CheckInAttendeeHandler;
use Evently\Services\Application\Handlers\Attendee\DTO\CheckInAttendeeDTO;
use Illuminate\Http\JsonResponse;

class CheckInAttendeeAction extends BaseAction
{
    public function __construct(
        private readonly CheckInAttendeeHandler $checkInAttendeeHandler
    )
    {
    }

    public function __invoke(CheckInAttendeeRequest $request, int $eventId, string $attendeePublicId): JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        $user = $this->getAuthenticatedUser();

        try {
            $attendee = $this->checkInAttendeeHandler->handle(CheckInAttendeeDTO::fromArray([
                'event_id' => $eventId,
                'attendee_public_id' => $attendeePublicId,
                'checked_in_by_user_id' => $user->getId(),
                'action' => $request->validated('action'),
            ]));
        } catch (CannotCheckInException $e) {
            return $this->errorResponse($e->getMessage(), ResponseCodes::HTTP_CONFLICT);
        }

        return $this->resourceResponse(AttendeeResource::class, $attendee);
    }
}
