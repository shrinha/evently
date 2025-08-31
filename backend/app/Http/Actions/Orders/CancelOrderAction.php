<?php

namespace Evently\Http\Actions\Orders;

use Evently\DomainObjects\EventDomainObject;
use Evently\DomainObjects\Status\OrderStatus;
use Evently\Exceptions\ResourceConflictException;
use Evently\Http\Actions\BaseAction;
use Evently\Resources\Order\OrderResource;
use Evently\Services\Application\Handlers\Order\CancelOrderHandler;
use Evently\Services\Application\Handlers\Order\DTO\CancelOrderDTO;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class CancelOrderAction extends BaseAction
{
    public function __construct(
        private readonly CancelOrderHandler $cancelOrderHandler,
    )
    {
    }

    public function __invoke(int $eventId, int $orderId): JsonResponse|Response
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        try {
            $order = $this->cancelOrderHandler->handle(new CancelOrderDTO($eventId, $orderId));
        } catch (ResourceConflictException $e) {
            return $this->errorResponse($e->getMessage(), HttpResponse::HTTP_CONFLICT);
        }

        return $this->resourceResponse(OrderResource::class, $order->setStatus(OrderStatus::CANCELLED->name));
    }
}
