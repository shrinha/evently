<?php

namespace Evently\Services\Domain\Product\DTO;

use Evently\DataTransferObjects\BaseDTO;

class OrderProductPriceDTO extends BaseDTO
{
    public function __construct(
        public readonly int    $quantity,
        public readonly int    $price_id,
        public readonly ?float $price = null // used for donation products
    )
    {
    }
}
