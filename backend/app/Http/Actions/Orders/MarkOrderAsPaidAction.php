<?php

namespace Evently\Http\Actions\Orders;

use Evently\DomainObjects\EventDomainObject;
use Evently\Exceptions\ResourceConflictException;
use Evently\Http\Actions\BaseAction;
use Evently\Resources\Order\OrderResource;
use Evently\Services\Application\Handlers\Order\DTO\MarkOrderAsPaidDTO;
use Evently\Services\Application\Handlers\Order\MarkOrderAsPaidHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class MarkOrderAsPaidAction extends BaseAction
{
    public function __construct(
        private readonly MarkOrderAsPaidHandler $markOrderAsPaidHandler,
    )
    {
    }

    public function __invoke(int $eventId, int $orderId): JsonResponse|Response
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        try {
            $order = $this->markOrderAsPaidHandler->handle(new MarkOrderAsPaidDTO($eventId, $orderId));
        } catch (ResourceConflictException $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_CONFLICT);
        }

        return $this->resourceResponse(
            resource: OrderResource::class,
            data: $order,
        );
    }
}
