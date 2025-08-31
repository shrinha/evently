<?php

declare(strict_types=1);

namespace Evently\Http\Actions\Products;

use Evently\DomainObjects\EventDomainObject;
use Evently\Exceptions\CannotChangeProductTypeException;
use Evently\Exceptions\InvalidTaxOrFeeIdException;
use Evently\Http\Actions\BaseAction;
use Evently\Http\Request\Product\UpsertProductRequest;
use Evently\Resources\Product\ProductResource;
use Evently\Services\Application\Handlers\Product\DTO\UpsertProductDTO;
use Evently\Services\Application\Handlers\Product\EditProductHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Throwable;

class EditProductAction extends BaseAction
{
    public function __construct(
        private readonly EditProductHandler $editProductHandler,
    )
    {
    }

    /**
     * @throws Throwable
     * @throws ValidationException
     */
    public function __invoke(UpsertProductRequest $request, int $eventId, int $productId): JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        $request->merge([
            'event_id' => $eventId,
            'account_id' => $this->getAuthenticatedAccountId(),
            'product_id' => $productId,
        ]);

        try {
            $product = $this->editProductHandler->handle(UpsertProductDTO::fromArray($request->all()));
        } catch (InvalidTaxOrFeeIdException $e) {
            throw ValidationException::withMessages([
                'tax_and_fee_ids' => $e->getMessage(),
            ]);
        } catch (CannotChangeProductTypeException $e) {
            throw ValidationException::withMessages([
                'type' => $e->getMessage(),
            ]);
        }

        return $this->resourceResponse(ProductResource::class, $product);
    }
}
