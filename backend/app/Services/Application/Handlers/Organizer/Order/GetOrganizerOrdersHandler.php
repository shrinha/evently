<?php

namespace Evently\Services\Application\Handlers\Organizer\Order;

use Evently\DomainObjects\AttendeeDomainObject;
use Evently\DomainObjects\InvoiceDomainObject;
use Evently\DomainObjects\OrderItemDomainObject;
use Evently\Http\DTO\QueryParamsDTO;
use Evently\Repository\Interfaces\OrderRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetOrganizerOrdersHandler
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
    )
    {
    }

    public function handle(int $organizer, int $accountId, QueryParamsDTO $queryParams): LengthAwarePaginator
    {
        return $this->orderRepository
            ->loadRelation(OrderItemDomainObject::class)
            ->loadRelation(AttendeeDomainObject::class)
            ->loadRelation(InvoiceDomainObject::class)
            ->findByOrganizerId(
                organizerId: $organizer,
                accountId: $accountId,
                params: $queryParams,
            );
    }
}
