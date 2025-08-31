<?php

namespace Evently\Http\Actions\CheckInLists\Public;

use Evently\Exceptions\CannotCheckInException;
use Evently\Http\Actions\BaseAction;
use Evently\Http\DTO\QueryParamsDTO;
use Evently\Resources\Attendee\AttendeeWithCheckInPublicResource;
use Evently\Services\Application\Handlers\CheckInList\Public\GetCheckInListAttendeesPublicHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class GetCheckInListAttendeesPublicAction extends BaseAction
{
    public function __construct(
        private readonly GetCheckInListAttendeesPublicHandler $getCheckInListAttendeesPublicHandler,
    )
    {
    }

    public function __invoke(string $uuid, Request $request): JsonResponse
    {
        try {
            $attendees = $this->getCheckInListAttendeesPublicHandler->handle(
                shortId: $uuid,
                queryParams: QueryParamsDTO::fromArray($request->query->all())
            );
        } catch (CannotCheckInException $e) {
            return $this->errorResponse(
                message: $e->getMessage(),
                statusCode: Response::HTTP_FORBIDDEN,
            );
        }

        return $this->resourceResponse(
            resource: AttendeeWithCheckInPublicResource::class,
            data: $attendees,
        );
    }
}
