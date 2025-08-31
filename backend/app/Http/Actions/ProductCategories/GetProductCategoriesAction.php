<?php

namespace Evently\Http\Actions\ProductCategories;

use Evently\DomainObjects\EventDomainObject;
use Evently\Http\Actions\BaseAction;
use Evently\Resources\ProductCategory\ProductCategoryResource;
use Evently\Services\Application\Handlers\ProductCategory\GetProductCategoriesHandler;
use Illuminate\Http\JsonResponse;

class GetProductCategoriesAction extends BaseAction
{
    public function __construct(
        private readonly GetProductCategoriesHandler $getProductCategoriesHandler,
    )
    {
    }

    public function __invoke(int $eventId): JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        $categories = $this->getProductCategoriesHandler->handle($eventId);

        return $this->resourceResponse(
            resource: ProductCategoryResource::class,
            data: $categories,
        );
    }
}
