<?php

namespace Evently\Repository\Interfaces;

use Evently\DomainObjects\ProductCategoryDomainObject;
use Evently\Http\DTO\QueryParamsDTO;
use Evently\Repository\Eloquent\BaseRepository;
use Illuminate\Support\Collection;

/**
 * @extends BaseRepository<ProductCategoryDomainObject>
 */
interface ProductCategoryRepositoryInterface extends RepositoryInterface
{
    public function findByEventId(int $eventId, QueryParamsDTO $queryParamsDTO): Collection;

    public function getNextOrder(int $eventId);
}
