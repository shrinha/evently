<?php

namespace Evently\DomainObjects\Status;

use Evently\DomainObjects\Enums\BaseEnum;

enum ProductStatus
{
    use BaseEnum;

    case ACTIVE;
    case INACTIVE;
}
