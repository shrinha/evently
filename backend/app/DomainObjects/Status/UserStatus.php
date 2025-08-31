<?php

namespace Evently\DomainObjects\Status;

use Evently\DomainObjects\Enums\BaseEnum;

enum UserStatus
{
    use BaseEnum;

    case ACTIVE;
    case INVITED;
    case INACTIVE;
}
