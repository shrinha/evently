<?php

namespace Evently\Http\Actions\Orders\Public;

use Evently\Http\Actions\BaseAction;
use Evently\Resources\Order\OrderResourcePublic;
use Evently\Services\Application\Handlers\Order\DTO\GetOrderPublicDTO;
use Evently\Services\Application\Handlers\Order\GetOrderPublicHandler;
use Evently\Services\Infrastructure\Session\CheckoutSessionManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GetOrderActionPublic extends BaseAction
{
    public function __construct(
        private readonly GetOrderPublicHandler            $getOrderPublicHandler,
        private readonly CheckoutSessionManagementService $sessionService,
    )
    {
    }

    public function __invoke(int $eventId, string $orderShortId, Request $request): JsonResponse
    {
        $order = $this->getOrderPublicHandler->handle(new GetOrderPublicDTO(
            eventId: $eventId,
            orderShortId: $orderShortId,
            includeEventInResponse: $this->isIncludeRequested($request, 'event'),
        ));

        $response = $this->resourceResponse(
            resource: OrderResourcePublic::class,
            data: $order,
        );

        if ($request->query->has('session_identifier')) {
            $response->headers->setCookie(
                $this->sessionService->getSessionCookie()
            );
        }

        return $response;
    }
}
