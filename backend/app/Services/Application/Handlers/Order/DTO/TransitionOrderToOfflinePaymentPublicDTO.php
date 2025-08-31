<?php

namespace Evently\Services\Application\Handlers\Order\DTO;

use Evently\DataTransferObjects\BaseDTO;

class TransitionOrderToOfflinePaymentPublicDTO extends BaseDTO
{
    public function __construct(
        public readonly string $orderShortId,
    )
    {
    }
}
