<?php

namespace Evently\DomainObjects\Enums;

enum PriceDisplayMode
{
    use BaseEnum;

    case INCLUSIVE;
    case EXCLUSIVE;
}
