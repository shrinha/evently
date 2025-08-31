<?php

namespace Evently\Services\Domain\Product\DTO;

use Evently\DataTransferObjects\BaseDTO;

class PriceDTO extends BaseDTO
{
    public function __construct(
        public float $price,
        public ?float $price_before_discount = null,
    )
    {
    }
}
