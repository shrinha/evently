<?php

namespace Evently\Services\Domain\Product;

use Evently\DomainObjects\ProductDomainObject;
use Evently\Repository\Interfaces\ProductRepositoryInterface;

class ProductOrderingService
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository
    )
    {
    }

    public function getOrderForNewProduct(int $eventId, int $productCategoryId): int
    {
        return ($this->productRepository->findWhere([
                'event_id' => $eventId,
                'product_category_id' => $productCategoryId,
            ])
                ->max((static fn(ProductDomainObject $product) => $product->getOrder())) ?? 0) + 1;
    }
}
