<?php

namespace Evently\Services\Domain\ProductCategory;

use Evently\DomainObjects\EventDomainObject;
use Evently\DomainObjects\ProductCategoryDomainObject;
use Evently\Repository\Interfaces\ProductCategoryRepositoryInterface;

class CreateProductCategoryService
{
    public function __construct(
        private readonly ProductCategoryRepositoryInterface $productCategoryRepository,
    )
    {
    }

    public function createCategory(ProductCategoryDomainObject $productCategoryDomainObject): ProductCategoryDomainObject
    {
        return $this->productCategoryRepository->create(array_filter($productCategoryDomainObject->toArray()));
    }

    public function createDefaultProductCategory(EventDomainObject $event): void
    {
        $this->createCategory((new ProductCategoryDomainObject())
            ->setEventId($event->getId())
            ->setName(__('Tickets'))
            ->setIsHidden(false)
            ->setNoProductsMessage(__('There are no tickets available for this event'))
        );
    }
}
