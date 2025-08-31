<?php

namespace Evently\DomainObjects\Status;

use Evently\DomainObjects\Enums\BaseEnum;

enum WebhookStatus: string
{
    use BaseEnum;

    case ENABLED = 'ENABLED';
    case PAUSED = 'PAUSED';
}
