<?php

namespace Evently\Repository\Eloquent;

use Evently\DomainObjects\OrderItemDomainObject;
use Evently\Models\OrderItem;
use Evently\Repository\Interfaces\OrderItemRepositoryInterface;

class OrderItemRepository extends BaseRepository implements OrderItemRepositoryInterface
{
    protected function getModel(): string
    {
        return OrderItem::class;
    }

    public function getDomainObject(): string
    {
        return OrderItemDomainObject::class;
    }
}
