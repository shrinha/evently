<?php

namespace Evently\Http\Actions\ProductCategories;

use Evently\DomainObjects\EventDomainObject;
use Evently\Http\Actions\BaseAction;
use Evently\Http\Request\ProductCategory\UpsertProductCategoryRequest;
use Evently\Resources\ProductCategory\ProductCategoryResource;
use Evently\Services\Application\Handlers\ProductCategory\DTO\UpsertProductCategoryDTO;
use Evently\Services\Application\Handlers\ProductCategory\EditProductCategoryHandler;
use Illuminate\Http\JsonResponse;

class EditProductCategoryAction extends BaseAction
{
    public function __construct(
        private readonly EditProductCategoryHandler $editProductCategoryHandler,
    )
    {
    }

    public function __invoke(UpsertProductCategoryRequest $request, int $eventId, int $productCategoryId): JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        $request->merge([
            'event_id' => $eventId,
            'account_id' => $this->getAuthenticatedAccountId(),
            'product_category_id' => $productCategoryId,
        ]);

        $productCategory = $this->editProductCategoryHandler->handle(new UpsertProductCategoryDTO(
            name: $request->validated('name'),
            description: $request->validated('description'),
            is_hidden: $request->validated('is_hidden'),
            event_id: $eventId,
            no_products_message: $request->validated('no_products_message'),
            product_category_id: $productCategoryId,
        ));

        return $this->resourceResponse(ProductCategoryResource::class, $productCategory);
    }
}
