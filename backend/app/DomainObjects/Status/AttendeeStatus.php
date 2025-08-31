<?php

namespace Evently\DomainObjects\Status;

use Evently\DomainObjects\Enums\BaseEnum;

enum AttendeeStatus
{
    use BaseEnum;

    case ACTIVE;
    case AWAITING_PAYMENT;
    case CANCELLED;
}
