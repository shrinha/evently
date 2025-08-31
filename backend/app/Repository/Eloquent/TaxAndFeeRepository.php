<?php

namespace Evently\Repository\Eloquent;

use Evently\DomainObjects\TaxAndFeesDomainObject;
use Evently\Models\TaxAndFee;
use Evently\Repository\Interfaces\TaxAndFeeRepositoryInterface;

class TaxAndFeeRepository extends BaseRepository implements TaxAndFeeRepositoryInterface
{
    public function getDomainObject(): string
    {
        return TaxAndFeesDomainObject::class;
    }

    protected function getModel(): string
    {
        return TaxAndFee::class;
    }
}
