<?php

namespace Evently\DomainObjects\Enums;

enum ProductType
{
    use BaseEnum;

    case TICKET;
    case GENERAL;
}
