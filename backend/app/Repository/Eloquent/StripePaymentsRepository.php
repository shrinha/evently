<?php

namespace Evently\Repository\Eloquent;

use Evently\DomainObjects\StripePaymentDomainObject;
use Evently\Models\StripePayment;
use Evently\Repository\Interfaces\StripePaymentsRepositoryInterface;

class StripePaymentsRepository extends BaseRepository implements StripePaymentsRepositoryInterface
{
    protected function getModel(): string
    {
        return StripePayment::class;
    }

    public function getDomainObject(): string
    {
        return StripePaymentDomainObject::class;
    }
}
