<?php

namespace Evently\DomainObjects\Enums;

enum PromoCodeDiscountTypeEnum
{
    use BaseEnum;

    case NONE;
    case FIXED;
    case PERCENTAGE;
}
