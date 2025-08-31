<?php

namespace Evently\Services\Application\Handlers\Order\DTO;

use Evently\DataTransferObjects\BaseDTO;

class GetOrderInvoiceDTO extends BaseDTO
{
    public function __construct(
        public readonly int $orderId,
        public readonly int $eventId,
    )
    {
    }
}
