<?php

declare(strict_types=1);

namespace Evently\Http\Actions\Products;

use Evently\DomainObjects\EventDomainObject;
use Evently\Exceptions\InvalidTaxOrFeeIdException;
use Evently\Http\Actions\BaseAction;
use Evently\Http\Request\Product\UpsertProductRequest;
use Evently\Http\ResponseCodes;
use Evently\Resources\Product\ProductResource;
use Evently\Services\Application\Handlers\Product\CreateProductHandler;
use Evently\Services\Application\Handlers\Product\DTO\UpsertProductDTO;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Throwable;

class CreateProductAction extends BaseAction
{
    private CreateProductHandler $createProductHandler;

    public function __construct(CreateProductHandler $handler)
    {
        $this->createProductHandler = $handler;
    }

    /**
     * @throws Throwable
     */
    public function __invoke(int $eventId, UpsertProductRequest $request): JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        $request->merge([
            'event_id' => $eventId,
            'account_id' => $this->getAuthenticatedAccountId(),
        ]);

        try {
            $product = $this->createProductHandler->handle(UpsertProductDTO::fromArray($request->all()));
        } catch (InvalidTaxOrFeeIdException $e) {
            throw ValidationException::withMessages([
                'tax_and_fee_ids' => $e->getMessage(),
            ]);
        }

        return $this->resourceResponse(
            resource: ProductResource::class,
            data: $product,
            statusCode: ResponseCodes::HTTP_CREATED,
        );
    }
}
