<?php

namespace Evently\Services\Application\Handlers\Account\Payment\Stripe\DTO;

use Evently\DataTransferObjects\BaseDTO;

class CreateStripeConnectAccountDTO extends BaseDTO
{
    public function __construct(
        public readonly int $accountId,
    )
    {
    }
}
