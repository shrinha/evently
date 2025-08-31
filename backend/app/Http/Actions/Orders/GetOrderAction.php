<?php

namespace Evently\Http\Actions\Orders;

use Evently\DomainObjects\AttendeeDomainObject;
use Evently\DomainObjects\EventDomainObject;
use Evently\DomainObjects\OrderItemDomainObject;
use Evently\DomainObjects\QuestionAndAnswerViewDomainObject;
use Evently\Http\Actions\BaseAction;
use Evently\Repository\Eloquent\Value\OrderAndDirection;
use Evently\Repository\Eloquent\Value\Relationship;
use Evently\Repository\Interfaces\OrderRepositoryInterface;
use Evently\Resources\Order\OrderResource;
use Illuminate\Http\JsonResponse;

class GetOrderAction extends BaseAction
{
    private OrderRepositoryInterface $orderRepository;

    public function __construct(OrderRepositoryInterface $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    public function __invoke(int $eventId, int $orderId): JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        $order = $this->orderRepository
            ->loadRelation(OrderItemDomainObject::class)
            ->loadRelation(AttendeeDomainObject::class)
            ->loadRelation(new Relationship(domainObject: QuestionAndAnswerViewDomainObject::class, orderAndDirections: [
                new OrderAndDirection(order: 'question_id'),
            ]))
            ->findById($orderId);

        return $this->resourceResponse(OrderResource::class, $order);
    }
}
