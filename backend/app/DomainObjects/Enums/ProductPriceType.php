<?php

namespace Evently\DomainObjects\Enums;

enum ProductPriceType
{
    use BaseEnum;

    case PAID;
    case FREE;
    case DONATION;
    case TIERED;
    case REGISTRATION;
}
