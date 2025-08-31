<?php

namespace Evently\Http\Actions\Orders;

use Evently\DomainObjects\AttendeeDomainObject;
use Evently\DomainObjects\EventDomainObject;
use Evently\DomainObjects\InvoiceDomainObject;
use Evently\DomainObjects\OrderDomainObject;
use Evently\DomainObjects\OrderItemDomainObject;
use Evently\Http\Actions\BaseAction;
use Evently\Repository\Interfaces\OrderRepositoryInterface;
use Evently\Resources\Order\OrderResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GetOrdersAction extends BaseAction
{
    private OrderRepositoryInterface $orderRepository;

    public function __construct(OrderRepositoryInterface $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    public function __invoke(Request $request, int $eventId): JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        $orders = $this->orderRepository
            ->loadRelation(OrderItemDomainObject::class)
            ->loadRelation(AttendeeDomainObject::class)
            ->loadRelation(InvoiceDomainObject::class)
            ->findByEventId($eventId, $this->getPaginationQueryParams($request));

        return $this->filterableResourceResponse(
            resource: OrderResource::class,
            data: $orders,
            domainObject: OrderDomainObject::class
        );
    }
}
