<?php

namespace Evently\Services\Application\Handlers\Order\DTO;

use Evently\DataTransferObjects\BaseDTO;

class CreatedProductDataDTO extends BaseDTO
{
    public function __construct(
        public readonly CompleteOrderProductDataDTO $productRequestData,
        public readonly ?string                      $shortId,
    )
    {
    }
}
