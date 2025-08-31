<?php

declare(strict_types=1);

namespace Evently\DomainObjects\Status;

use Evently\DomainObjects\Enums\BaseEnum;

enum AffiliateStatus: string
{
    use BaseEnum;

    case ACTIVE = 'ACTIVE';
    case INACTIVE = 'INACTIVE';
}
