<?php

namespace Evently\Listeners\Order;

use Evently\Events\OrderStatusChangedEvent;
use Evently\Jobs\Order\SendOrderDetailsEmailJob;

class SendOrderDetailsEmailListener
{
    public function handle(OrderStatusChangedEvent $changedEvent): void
    {
        if (!$changedEvent->sendEmails) {
            return;
        }

        dispatch(new SendOrderDetailsEmailJob($changedEvent->order));
    }
}
