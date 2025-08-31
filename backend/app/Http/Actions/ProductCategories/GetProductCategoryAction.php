<?php

namespace Evently\Http\Actions\ProductCategories;

use Evently\DomainObjects\EventDomainObject;
use Evently\Http\Actions\BaseAction;
use Evently\Resources\ProductCategory\ProductCategoryResource;
use Evently\Services\Application\Handlers\ProductCategory\GetProductCategoryHandler;
use Illuminate\Http\JsonResponse;

class GetProductCategoryAction extends BaseAction
{
    public function __construct(
        private readonly GetProductCategoryHandler $getProductCategoryHandler,
    )
    {
    }

    public function __invoke(int $eventId, int $productCategoryId): JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        $category = $this->getProductCategoryHandler->handle($eventId, $productCategoryId);

        return $this->resourceResponse(
            resource: ProductCategoryResource::class,
            data: $category,
        );
    }
}
