<?php

namespace Evently\Http\Actions\CheckInLists;

use Evently\DomainObjects\EventDomainObject;
use Evently\Http\Actions\BaseAction;
use Evently\Http\Request\CheckInList\UpsertCheckInListRequest;
use Evently\Resources\CheckInList\CheckInListResource;
use Evently\Services\Application\Handlers\CheckInList\CreateCheckInListHandler;
use Evently\Services\Application\Handlers\CheckInList\DTO\UpsertCheckInListDTO;
use Evently\Services\Domain\Product\Exception\UnrecognizedProductIdException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class CreateCheckInListAction extends BaseAction
{
    public function __construct(
        private readonly CreateCheckInListHandler $checkInListHandler,
    )
    {
    }

    public function __invoke(UpsertCheckInListRequest $request, int $eventId): JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        try {
            $checkInList = $this->checkInListHandler->handle(
                new UpsertCheckInListDTO(
                    name: $request->validated('name'),
                    description: $request->validated('description'),
                    eventId: $eventId,
                    productIds: $request->validated('product_ids'),
                    expiresAt: $request->validated('expires_at'),
                    activatesAt: $request->validated('activates_at'),
                )
            );
        } catch (UnrecognizedProductIdException $exception) {
            return $this->errorResponse(
                message: $exception->getMessage(),
                statusCode: Response::HTTP_UNPROCESSABLE_ENTITY,
            );
        }

        return $this->resourceResponse(
            resource: CheckInListResource::class,
            data: $checkInList
        );
    }
}
