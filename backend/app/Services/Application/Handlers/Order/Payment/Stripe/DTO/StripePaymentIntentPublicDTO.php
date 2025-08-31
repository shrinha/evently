<?php

namespace Evently\Services\Application\Handlers\Order\Payment\Stripe\DTO;

use Evently\DataTransferObjects\BaseDTO;

class StripePaymentIntentPublicDTO extends BaseDTO
{
    public function __construct(
        public string $status,
        public string $paymentIntentId,
        public string $amount,
    )
    {
    }
}
