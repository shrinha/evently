<?php

namespace Evently\DomainObjects\Status;

use Evently\DomainObjects\Enums\BaseEnum;

enum OrganizerStatus: string
{
    use BaseEnum;

    case DRAFT = 'DRAFT';
    case LIVE = 'LIVE';
    case ARCHIVED = 'ARCHIVED';
}
