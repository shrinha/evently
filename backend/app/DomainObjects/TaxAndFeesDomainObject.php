<?php

namespace Evently\DomainObjects;

use Evently\DomainObjects\Enums\TaxType;

class TaxAndFeesDomainObject extends Generated\TaxAndFeesDomainObjectAbstract
{
    public function isTax(): bool
    {
        return $this->getType() === TaxType::TAX->name;
    }

    public function isFee(): bool
    {
        return $this->getType() === TaxType::FEE->name;
    }
}
