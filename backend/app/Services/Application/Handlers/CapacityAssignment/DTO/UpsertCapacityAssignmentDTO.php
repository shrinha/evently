<?php

namespace Evently\Services\Application\Handlers\CapacityAssignment\DTO;

use Evently\DataTransferObjects\BaseDTO;
use Evently\DomainObjects\Status\CapacityAssignmentStatus;

class UpsertCapacityAssignmentDTO extends BaseDTO
{
    public function __construct(
        public string                   $name,
        public int                      $event_id,
        public CapacityAssignmentStatus $status,

        public ?int                     $capacity,
        public ?array                   $product_ids = null,
        public ?int                     $id = null,
    )
    {
    }
}
