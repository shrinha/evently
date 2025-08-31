<?php

namespace Evently\Services\Application\Handlers\Order\DTO;

use Evently\DataTransferObjects\Attributes\CollectionOf;
use Evently\DataTransferObjects\BaseDTO;
use Evently\Services\Domain\Product\DTO\OrderProductPriceDTO;
use Illuminate\Support\Collection;

class ProductOrderDetailsDTO extends BaseDTO
{
    public function __construct(
        public readonly int $product_id,
        #[CollectionOf(OrderProductPriceDTO::class)]
        public Collection   $quantities,
    )
    {
    }
}
