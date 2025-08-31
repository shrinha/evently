<?php

namespace Evently\Services\Infrastructure\DomainEvents\Events;

use Evently\Services\Infrastructure\DomainEvents\Enums\DomainEventType;

class OrderEvent extends BaseDomainEvent
{
    public function __construct(
        public DomainEventType $type,
        public int $orderId,
    )
    {
    }
}
