<?php

namespace Evently\Services\Application\Handlers\Order\DTO;

use Evently\DataTransferObjects\BaseDTO;

class CancelOrderDTO extends BaseDTO
{
    public function __construct(
        public int $eventId,
        public int $orderId
    )
    {
    }
}
