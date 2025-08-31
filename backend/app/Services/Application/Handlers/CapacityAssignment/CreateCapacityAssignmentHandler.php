<?php

namespace Evently\Services\Application\Handlers\CapacityAssignment;

use Evently\DomainObjects\CapacityAssignmentDomainObject;
use Evently\DomainObjects\Enums\CapacityAssignmentAppliesTo;
use Evently\Services\Application\Handlers\CapacityAssignment\DTO\UpsertCapacityAssignmentDTO;
use Evently\Services\Domain\CapacityAssignment\CreateCapacityAssignmentService;
use Evently\Services\Domain\Product\Exception\UnrecognizedProductIdException;

class CreateCapacityAssignmentHandler
{
    public function __construct(
        private readonly CreateCapacityAssignmentService $createCapacityAssignmentService
    )
    {
    }

    /**
     * @throws UnrecognizedProductIdException
     */
    public function handle(UpsertCapacityAssignmentDTO $data): CapacityAssignmentDomainObject
    {
        $capacityAssignment = (new CapacityAssignmentDomainObject)
            ->setName($data->name)
            ->setEventId($data->event_id)
            ->setCapacity($data->capacity)
            ->setAppliesTo(CapacityAssignmentAppliesTo::PRODUCTS->name)
            ->setStatus($data->status->name);

        return $this->createCapacityAssignmentService->createCapacityAssignment(
            $capacityAssignment,
            $data->product_ids,
        );
    }
}
