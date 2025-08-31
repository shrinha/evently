<?php

namespace Evently\Services\Application\Handlers\Product;

use Evently\DomainObjects\ProductPriceDomainObject;
use Evently\DomainObjects\TaxAndFeesDomainObject;
use Evently\Http\DTO\QueryParamsDTO;
use Evently\Repository\Interfaces\ProductRepositoryInterface;
use Evently\Services\Domain\Product\ProductFilterService;
use Illuminate\Pagination\LengthAwarePaginator;

class GetProductsHandler
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
        private readonly ProductFilterService       $productFilterService,
    )
    {
    }

    public function handle(int $eventId, QueryParamsDTO $queryParamsDTO): LengthAwarePaginator
    {
        $productPaginator = $this->productRepository
            ->loadRelation(ProductPriceDomainObject::class)
            ->loadRelation(TaxAndFeesDomainObject::class)
            ->findByEventId($eventId, $queryParamsDTO);

        $filteredProducts = $this->productFilterService->filter(
            productsCategories: $productPaginator->getCollection(),
            hideSoldOutProducts: false,
        );

        $productPaginator->setCollection($filteredProducts);

        return $productPaginator;
    }
}
