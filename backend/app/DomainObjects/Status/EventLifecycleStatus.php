<?php

namespace Evently\DomainObjects\Status;

use Evently\DomainObjects\Enums\BaseEnum;

enum EventLifecycleStatus
{
    use BaseEnum;

    case UPCOMING;
    case ENDED;
    case ONGOING;
}
