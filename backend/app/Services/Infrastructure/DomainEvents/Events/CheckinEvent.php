<?php

namespace Evently\Services\Infrastructure\DomainEvents\Events;

use Evently\Services\Infrastructure\DomainEvents\Enums\DomainEventType;

class CheckinEvent extends BaseDomainEvent
{
    public function __construct(
        public DomainEventType $type,
        public int             $attendeeCheckinId,
    )
    {
    }
}
