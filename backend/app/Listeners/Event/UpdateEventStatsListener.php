<?php

namespace Evently\Listeners\Event;

use Evently\Events\OrderStatusChangedEvent;
use Evently\Jobs\Event\UpdateEventStatisticsJob;

class UpdateEventStatsListener
{
    public function handle(OrderStatusChangedEvent $changedEvent): void
    {
        if (!$changedEvent->order->isOrderCompleted()) {
            return;
        }

        dispatch(new UpdateEventStatisticsJob($changedEvent->order));
    }
}
