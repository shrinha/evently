<?php

namespace Evently\Repository\Eloquent;

use Evently\DomainObjects\StripeCustomerDomainObject;
use Evently\Models\StripeCustomer;
use Evently\Repository\Interfaces\StripeCustomerRepositoryInterface;

class StripeCustomerRepository extends BaseRepository implements StripeCustomerRepositoryInterface
{
    protected function getModel(): string
    {
        return StripeCustomer::class;
    }

    public function getDomainObject(): string
    {
        return StripeCustomerDomainObject::class;
    }
}
