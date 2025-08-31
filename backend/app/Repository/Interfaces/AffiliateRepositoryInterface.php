<?php

declare(strict_types=1);

namespace Evently\Repository\Interfaces;

use Evently\Http\DTO\QueryParamsDTO;
use Illuminate\Pagination\LengthAwarePaginator;

interface AffiliateRepositoryInterface extends RepositoryInterface
{
    public function findByEventId(int $eventId, QueryParamsDTO $params): LengthAwarePaginator;

    public function incrementSales(int $affiliateId, float $amount): void;
}
