<?php

declare(strict_types=1);

namespace Evently\Repository\Interfaces;

use Evently\DomainObjects\EventDomainObject;
use Evently\Http\DTO\QueryParamsDTO;
use Evently\Repository\Eloquent\BaseRepository;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * @extends BaseRepository<EventDomainObject>
 */
interface EventRepositoryInterface extends RepositoryInterface
{
    public function findEventsForOrganizer(int $organizerId, int $accountId, QueryParamsDTO $params): LengthAwarePaginator;

    public function findEvents(array $where, QueryParamsDTO $params): LengthAwarePaginator;
}
