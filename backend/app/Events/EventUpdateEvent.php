<?php

namespace Evently\Events;

use Evently\DomainObjects\EventDomainObject;
use Illuminate\Foundation\Events\Dispatchable;

class EventUpdateEvent
{
    use Dispatchable;

    public function __construct(
        private readonly EventDomainObject $event,
    )
    {
    }
}
