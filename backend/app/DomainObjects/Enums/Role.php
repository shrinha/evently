<?php

namespace Evently\DomainObjects\Enums;

enum Role: string
{
    use BaseEnum;

    case ADMIN = 'ADMIN';
    case ORGANIZER = 'ORGANIZER';
}
