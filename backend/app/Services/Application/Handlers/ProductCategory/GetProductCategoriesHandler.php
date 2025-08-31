<?php

namespace Evently\Services\Application\Handlers\ProductCategory;

use Evently\DomainObjects\Generated\ProductCategoryDomainObjectAbstract;
use Evently\DomainObjects\Generated\ProductDomainObjectAbstract;
use Evently\DomainObjects\ProductDomainObject;
use Evently\DomainObjects\ProductPriceDomainObject;
use Evently\DomainObjects\TaxAndFeesDomainObject;
use Evently\Repository\Eloquent\Value\OrderAndDirection;
use Evently\Repository\Eloquent\Value\Relationship;
use Evently\Repository\Interfaces\ProductCategoryRepositoryInterface;
use Illuminate\Support\Collection;

class GetProductCategoriesHandler
{
    public function __construct(
        private readonly ProductCategoryRepositoryInterface $productCategoryRepository,
    )
    {
    }

    public function handle(int $eventId): Collection
    {
        return $this->productCategoryRepository
            ->loadRelation(new Relationship(
                domainObject: ProductDomainObject::class,
                nested: [
                    new Relationship(ProductPriceDomainObject::class),
                    new Relationship(TaxAndFeesDomainObject::class),
                ],
                orderAndDirections: [
                    new OrderAndDirection(
                        order: ProductDomainObjectAbstract::ORDER,
                    ),
                ],
            ))
            ->findWhere(
                where: [
                    'event_id' => $eventId,
                ],
                orderAndDirections: [
                    new OrderAndDirection(
                        order: ProductCategoryDomainObjectAbstract::ORDER,
                    ),
                ],
            );
    }
}
