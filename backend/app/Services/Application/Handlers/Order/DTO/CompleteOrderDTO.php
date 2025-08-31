<?php

namespace Evently\Services\Application\Handlers\Order\DTO;

use Evently\DataTransferObjects\Attributes\CollectionOf;
use Evently\DataTransferObjects\BaseDTO;
use Illuminate\Support\Collection;

class CompleteOrderDTO extends BaseDTO
{
    /**
     * @param CompleteOrderOrderDTO $order
     * @param Collection<CompleteOrderProductDataDTO> $products
     */
    public function __construct(
        public CompleteOrderOrderDTO $order,
        #[CollectionOf(CompleteOrderProductDataDTO::class)]
        public Collection $products
    )
    {
    }
}
