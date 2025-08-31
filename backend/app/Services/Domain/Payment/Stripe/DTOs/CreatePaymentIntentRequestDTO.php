<?php

namespace Evently\Services\Domain\Payment\Stripe\DTOs;

use Evently\DataTransferObjects\BaseDTO;
use Evently\DomainObjects\AccountDomainObject;
use Evently\DomainObjects\OrderDomainObject;
use Evently\Values\MoneyValue;

class CreatePaymentIntentRequestDTO extends BaseDTO
{
    public function __construct(
        public readonly MoneyValue $amount,
        public readonly string     $currencyCode,
        public AccountDomainObject $account,
        public OrderDomainObject   $order,
    )
    {
    }
}
