<?php

namespace Evently\DomainObjects\Enums;

enum TaxType
{
    use BaseEnum;

    case TAX;
    case FEE;
}
