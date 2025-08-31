<?php

declare(strict_types=1);

namespace Evently\Http\Actions\Orders\Public;

use Evently\Exceptions\ResourceConflictException;
use Evently\Http\Actions\BaseAction;
use Evently\Http\Request\Order\CompleteOrderRequest;
use Evently\Resources\Order\OrderResourcePublic;
use Evently\Services\Application\Handlers\Order\CompleteOrderHandler;
use Evently\Services\Application\Handlers\Order\DTO\CompleteOrderDTO;
use Evently\Services\Application\Handlers\Order\DTO\CompleteOrderOrderDTO;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class CompleteOrderActionPublic extends BaseAction
{
    public function __construct(private readonly CompleteOrderHandler $orderService)
    {
    }

    public function __invoke(CompleteOrderRequest $request, int $eventId, string $orderShortId): JsonResponse
    {
        try {
            $order = $this->orderService->handle($orderShortId, CompleteOrderDTO::fromArray([
                'order' => CompleteOrderOrderDTO::fromArray([
                    'first_name' => $request->validated('order.first_name'),
                    'last_name' => $request->validated('order.last_name'),
                    'email' => $request->validated('order.email'),
                    'address' => $request->validated('order.address'),
                    'questions' => $request->has('order.questions')
                        ? $request->input('order.questions')
                        : null,
                ]),
                'products' => $request->input('products'),
            ]));
        } catch (ResourceConflictException $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_CONFLICT);
        }

        return $this->resourceResponse(OrderResourcePublic::class, $order);
    }
}
