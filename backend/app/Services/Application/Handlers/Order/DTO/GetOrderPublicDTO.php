<?php

namespace Evently\Services\Application\Handlers\Order\DTO;

use Evently\DataTransferObjects\BaseDTO;

class GetOrderPublicDTO extends BaseDTO
{
    public function __construct(
        public int    $eventId,
        public string $orderShortId,
        public bool   $includeEventInResponse = false,
    )
    {
    }
}
