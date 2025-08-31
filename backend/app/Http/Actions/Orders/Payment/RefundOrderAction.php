<?php

namespace Evently\Http\Actions\Orders\Payment;

use Evently\DomainObjects\EventDomainObject;
use Evently\Exceptions\RefundNotPossibleException;
use Evently\Http\Actions\BaseAction;
use Evently\Http\Request\Order\RefundOrderRequest;
use Evently\Resources\Order\OrderResource;
use Evently\Services\Application\Handlers\Order\DTO\RefundOrderDTO;
use Evently\Services\Application\Handlers\Order\Payment\Stripe\RefundOrderHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Stripe\Exception\ApiErrorException;
use Throwable;

class RefundOrderAction extends BaseAction
{
    public function __construct(private readonly RefundOrderHandler     $refundOrderHandler)
    {
    }

    /**
     * @throws Throwable
     * @throws ValidationException
     */
    public function __invoke(RefundOrderRequest $request, int $eventId, int $orderId): JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        try {
            $order = $this->refundOrderHandler->handle(
                refundOrderDTO: RefundOrderDTO::fromArray(array_merge($request->validated(), [
                    'event_id' => $eventId,
                    'order_id' => $orderId,
                ]))
            );
        } catch (ApiErrorException|RefundNotPossibleException $exception) {
            throw ValidationException::withMessages([
                'amount' => $exception instanceof ApiErrorException
                    ? 'Stripe error: ' . $exception->getMessage()
                    : $exception->getMessage(),
            ]);
        }

        return $this->resourceResponse(OrderResource::class, $order);
    }
}
