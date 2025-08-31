<?php

namespace Evently\Repository\Eloquent;

use Evently\DomainObjects\OrderRefundDomainObject;
use Evently\Models\OrderRefund;
use Evently\Repository\Interfaces\OrderRefundRepositoryInterface;

class OrderRefundRepository extends BaseRepository implements OrderRefundRepositoryInterface
{
    protected function getModel(): string
    {
        return OrderRefund::class;
    }

    public function getDomainObject(): string
    {
        return OrderRefundDomainObject::class;
    }
}
