<?php

namespace Evently\Repository\Interfaces;

use Evently\DomainObjects\PromoCodeDomainObject;
use Evently\Http\DTO\QueryParamsDTO;
use Evently\Repository\Eloquent\BaseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * @extends BaseRepository<PromoCodeDomainObject>
 */
interface PromoCodeRepositoryInterface extends RepositoryInterface
{
    public function findByEventId(int $eventId, QueryParamsDTO $params): LengthAwarePaginator;
}
