<?php

namespace Evently\DomainObjects\Status;

use Evently\DomainObjects\Enums\BaseEnum;

enum MessageStatus
{
    use BaseEnum;

    case PROCESSING;
    case SENT;
    case FAILED;
}
