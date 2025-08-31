<?php

namespace Evently\Http\Actions\CapacityAssignments;

use Evently\DomainObjects\EventDomainObject;
use Evently\Http\Actions\BaseAction;
use Evently\Http\Request\CapacityAssigment\UpsertCapacityAssignmentRequest;
use Evently\Resources\CapacityAssignment\CapacityAssignmentResource;
use Evently\Services\Application\Handlers\CapacityAssignment\CreateCapacityAssignmentHandler;
use Evently\Services\Application\Handlers\CapacityAssignment\DTO\UpsertCapacityAssignmentDTO;
use Evently\Services\Domain\Product\Exception\UnrecognizedProductIdException;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class CreateCapacityAssignmentAction extends BaseAction
{
    public function __construct(
        private readonly CreateCapacityAssignmentHandler $createCapacityAssignmentHandler,
    )
    {
    }

    public function __invoke(int $eventId, UpsertCapacityAssignmentRequest $request): JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        try {
            $assignment = $this->createCapacityAssignmentHandler->handle(
                UpsertCapacityAssignmentDTO::fromArray([
                    'name' => $request->validated('name'),
                    'event_id' => $eventId,
                    'capacity' => $request->validated('capacity'),
                    'status' => $request->validated('status'),
                    'product_ids' => $request->validated('product_ids'),
                ]),
            );
        } catch (UnrecognizedProductIdException $exception) {
            return $this->errorResponse(
                message: $exception->getMessage(),
                statusCode: Response::HTTP_UNPROCESSABLE_ENTITY,
            );
        }

        return $this->resourceResponse(
            resource: CapacityAssignmentResource::class,
            data: $assignment,
        );
    }
}
