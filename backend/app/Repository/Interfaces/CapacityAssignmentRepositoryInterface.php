<?php

namespace Evently\Repository\Interfaces;

use Evently\DomainObjects\CapacityAssignmentDomainObject;
use Evently\Http\DTO\QueryParamsDTO;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * @extends RepositoryInterface<CapacityAssignmentDomainObject>
 */
interface CapacityAssignmentRepositoryInterface extends RepositoryInterface
{
    public function findByEventId(int $eventId, QueryParamsDTO $params): LengthAwarePaginator;
}
