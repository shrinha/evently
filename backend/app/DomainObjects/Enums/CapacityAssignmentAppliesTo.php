<?php

namespace Evently\DomainObjects\Enums;

enum CapacityAssignmentAppliesTo
{
    use BaseEnum;

    case PRODUCTS;
    case EVENT;
}
