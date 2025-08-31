<?php

namespace Evently\DomainObjects\Enums;

enum TaxCalculationType
{
    use BaseEnum;

    case PERCENTAGE;
    case FIXED;
}
