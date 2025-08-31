<?php

namespace Evently\Http\Actions\Orders\Public;

use Evently\Http\Actions\BaseAction;
use Evently\Resources\Order\OrderResourcePublic;
use Evently\Services\Application\Handlers\Order\DTO\TransitionOrderToOfflinePaymentPublicDTO;
use Evently\Services\Application\Handlers\Order\TransitionOrderToOfflinePaymentHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransitionOrderToOfflinePaymentPublicAction extends BaseAction
{
    public function __construct(
        private readonly TransitionOrderToOfflinePaymentHandler $initializeOrderOfflinePaymentPublicHandler,
    )
    {
    }

    public function __invoke(Request $request, int $eventId, string $orderShortId): JsonResponse
    {
        $order = $this->initializeOrderOfflinePaymentPublicHandler->handle(
            TransitionOrderToOfflinePaymentPublicDTO::fromArray([
                'orderShortId' => $orderShortId,
            ]),
        );

        return $this->resourceResponse(
            resource: OrderResourcePublic::class,
            data: $order,
        );
    }
}
