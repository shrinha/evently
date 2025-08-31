<?php

namespace Evently\Repository\Interfaces;

use Evently\DomainObjects\MessageDomainObject;
use Evently\Http\DTO\QueryParamsDTO;
use Evently\Repository\Eloquent\BaseRepository;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * @extends BaseRepository<MessageDomainObject>
 */
interface MessageRepositoryInterface extends RepositoryInterface
{
    public function findByEventId(int $eventId, QueryParamsDTO $params): LengthAwarePaginator;
}
