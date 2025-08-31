<?php

namespace Evently\Services\Application\Handlers\CapacityAssignment\DTO;

use Evently\DataTransferObjects\BaseDTO;
use Evently\Http\DTO\QueryParamsDTO;

class GetCapacityAssignmentsDTO extends BaseDTO
{
    public function __construct(
        public int            $eventId,
        public QueryParamsDTO $queryParams,
    )
    {
    }
}
