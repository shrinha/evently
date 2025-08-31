<?php

namespace Evently\Repository\Eloquent;

use Evently\DomainObjects\ProductPriceDomainObject;
use Evently\Models\ProductPrice;
use Evently\Repository\Interfaces\ProductPriceRepositoryInterface;

class ProductPriceRepository extends BaseRepository implements ProductPriceRepositoryInterface
{
    protected function getModel(): string
    {
        return ProductPrice::class;
    }

    public function getDomainObject(): string
    {
        return ProductPriceDomainObject::class;
    }
}
