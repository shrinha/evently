<?php

namespace Evently\Services\Application\Handlers\Product;

use Evently\Exceptions\CannotDeleteEntityException;
use Evently\Services\Domain\Product\DeleteProductService;
use Throwable;

class DeleteProductHandler
{
    public function __construct(
        private readonly DeleteProductService $deleteProductService,
    )
    {
    }

    /**
     * @throws CannotDeleteEntityException
     * @throws Throwable
     */
    public function handle(int $productId, int $eventId): void
    {
        $this->deleteProductService->deleteProduct($productId, $eventId);
    }
}
