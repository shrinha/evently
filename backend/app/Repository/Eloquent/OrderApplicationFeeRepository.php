<?php

namespace Evently\Repository\Eloquent;

use Evently\DomainObjects\OrderApplicationFeeDomainObject;
use Evently\Models\OrderApplicationFee;
use Evently\Repository\Interfaces\OrderApplicationFeeRepositoryInterface;

class OrderApplicationFeeRepository extends BaseRepository implements OrderApplicationFeeRepositoryInterface
{
    protected function getModel(): string
    {
        return OrderApplicationFee::class;
    }

    public function getDomainObject(): string
    {
        return OrderApplicationFeeDomainObject::class;
    }
}
