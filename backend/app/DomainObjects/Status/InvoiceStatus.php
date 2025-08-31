<?php

namespace Evently\DomainObjects\Status;

use Evently\DomainObjects\Enums\BaseEnum;

enum InvoiceStatus
{
    use BaseEnum;

    case UNPAID;
    case PAID;
    case VOID;
}
