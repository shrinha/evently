<?php

namespace Evently\DomainObjects\Status;

use Evently\DomainObjects\Enums\BaseEnum;

enum EventStatus
{
    use BaseEnum;

    case DRAFT;
    case LIVE;
    case ARCHIVED;
}
