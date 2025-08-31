<?php

namespace Evently\Http\Actions\CheckInLists\Public;

use Evently\Exceptions\CannotCheckInException;
use Evently\Http\Actions\BaseAction;
use Evently\Http\Request\CheckInList\CreateAttendeeCheckInPublicRequest;
use Evently\Resources\CheckInList\AttendeeCheckInPublicResource;
use Evently\Services\Application\Handlers\CheckInList\Public\CreateAttendeeCheckInPublicHandler;
use Evently\Services\Application\Handlers\CheckInList\Public\DTO\CreateAttendeeCheckInPublicDTO;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class CreateAttendeeCheckInPublicAction extends BaseAction
{
    public function __construct(
        private readonly CreateAttendeeCheckInPublicHandler $createAttendeeCheckInPublicHandler,
    )
    {
    }

    public function __invoke(
        string                             $checkInListUuid,
        CreateAttendeeCheckInPublicRequest $request,
    ): JsonResponse
    {
        try {
            $checkIns = $this->createAttendeeCheckInPublicHandler->handle(CreateAttendeeCheckInPublicDTO::from([
                'checkInListUuid' => $checkInListUuid,
                'checkInUserIpAddress' => $request->ip(),
                'attendeesAndActions' => $request->validated('attendees'),
            ]));
        } catch (CannotCheckInException $e) {
            return $this->errorResponse(
                message: $e->getMessage(),
                statusCode: Response::HTTP_CONFLICT,
            );
        }

        return $this->resourceResponse(
            resource: AttendeeCheckInPublicResource::class,
            data: $checkIns->attendeeCheckIns,
            errors: $checkIns->errors->toArray()
        );
    }
}
