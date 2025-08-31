<?php

namespace Evently\Repository\Interfaces;

use Evently\DomainObjects\AttendeeDomainObject;
use Evently\Http\DTO\QueryParamsDTO;
use Evently\Repository\Eloquent\BaseRepository;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * @extends BaseRepository<AttendeeDomainObject>
 */
interface AttendeeRepositoryInterface extends RepositoryInterface
{
    public function findByEventId(int $eventId, QueryParamsDTO $params): LengthAwarePaginator;

    public function findByEventIdForExport(int $eventId): Collection;

    public function getAttendeesByCheckInShortId(string $shortId, QueryParamsDTO $params): Paginator;
}
