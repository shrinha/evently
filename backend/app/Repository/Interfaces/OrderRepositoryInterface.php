<?php

declare(strict_types=1);

namespace Evently\Repository\Interfaces;

use Evently\DomainObjects\OrderDomainObject;
use Evently\DomainObjects\OrderItemDomainObject;
use Evently\Http\DTO\QueryParamsDTO;
use Evently\Repository\Eloquent\BaseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * @extends BaseRepository<OrderDomainObject>
 */
interface OrderRepositoryInterface extends RepositoryInterface
{
    public function findByEventId(int $eventId, QueryParamsDTO $params): LengthAwarePaginator;

    public function findByOrganizerId(int $organizerId, int $accountId, QueryParamsDTO $params): LengthAwarePaginator;

    public function getOrderItems(int $orderId);

    public function getAttendees(int $orderId);

    public function addOrderItem(array $data): OrderItemDomainObject;

    public function findByShortId(string $orderShortId): ?OrderDomainObject;

    public function findOrdersAssociatedWithProducts(int $eventId, array $productIds, array $orderStatuses): Collection;
}
