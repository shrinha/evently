<?php

namespace Evently\Services\Application\Handlers\CapacityAssignment;

use Evently\DomainObjects\CapacityAssignmentDomainObject;
use Evently\DomainObjects\Enums\CapacityAssignmentAppliesTo;
use Evently\Services\Application\Handlers\CapacityAssignment\DTO\UpsertCapacityAssignmentDTO;
use Evently\Services\Domain\CapacityAssignment\UpdateCapacityAssignmentService;
use Evently\Services\Domain\Product\Exception\UnrecognizedProductIdException;

class UpdateCapacityAssignmentHandler
{
    public function __construct(
        private readonly UpdateCapacityAssignmentService $updateCapacityAssignmentService,
    )
    {
    }

    /**
     * @throws UnrecognizedProductIdException
     */
    public function handle(UpsertCapacityAssignmentDTO $data): CapacityAssignmentDomainObject
    {
        $capacityAssignment = (new CapacityAssignmentDomainObject)
            ->setId($data->id)
            ->setName($data->name)
            ->setEventId($data->event_id)
            ->setCapacity($data->capacity)
            ->setAppliesTo(CapacityAssignmentAppliesTo::PRODUCTS->name)
            ->setStatus($data->status->name);

        return $this->updateCapacityAssignmentService->updateCapacityAssignment(
            $capacityAssignment,
            $data->product_ids,
        );
    }
}
