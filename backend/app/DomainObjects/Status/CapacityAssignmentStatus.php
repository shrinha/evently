<?php

namespace Evently\DomainObjects\Status;

use Evently\DomainObjects\Enums\BaseEnum;

enum CapacityAssignmentStatus
{
    use BaseEnum;

    case ACTIVE;
    case INACTIVE;
}
