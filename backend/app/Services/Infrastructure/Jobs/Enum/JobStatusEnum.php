<?php

namespace Evently\Services\Infrastructure\Jobs\Enum;

use Evently\DomainObjects\Enums\BaseEnum;

enum JobStatusEnum
{
    use BaseEnum;

    case IN_PROGRESS;
    case FINISHED;
    case FAILED;
    case NOT_FOUND;
}
