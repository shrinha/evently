<?php

namespace Evently\Listeners\Webhook;

use Evently\Jobs\Order\Webhook\DispatchAttendeeWebhookJob;
use Evently\Jobs\Order\Webhook\DispatchCheckInWebhookJob;
use Evently\Jobs\Order\Webhook\DispatchOrderWebhookJob;
use Evently\Jobs\Order\Webhook\DispatchProductWebhookJob;
use Evently\Services\Infrastructure\DomainEvents\Events\AttendeeEvent;
use Evently\Services\Infrastructure\DomainEvents\Events\BaseDomainEvent;
use Evently\Services\Infrastructure\DomainEvents\Events\CheckinEvent;
use Evently\Services\Infrastructure\DomainEvents\Events\OrderEvent;
use Evently\Services\Infrastructure\DomainEvents\Events\ProductEvent;
use Illuminate\Config\Repository;

class WebhookEventListener
{
    public function __construct(
        private readonly Repository $config,
    )
    {
    }

    public function handle(BaseDomainEvent $event): void
    {
        $queueName = $this->config->get('queue.webhook_queue_name');

        switch (get_class($event)) {
            case AttendeeEvent::class:
                DispatchAttendeeWebhookJob::dispatch(
                    attendeeId: $event->attendeeId,
                    eventType: $event->type,
                )->onQueue($queueName);
                break;
            case OrderEvent::class:
                DispatchOrderWebhookJob::dispatch(
                    orderId: $event->orderId,
                    eventType: $event->type,
                )->onQueue($queueName);
                break;
            case ProductEvent::class:
                DispatchProductWebhookJob::dispatch(
                    productId: $event->productId,
                    eventType: $event->type,
                )->onQueue($queueName);
                break;
            case CheckinEvent::class:
                DispatchCheckInWebhookJob::dispatch(
                    attendeeCheckInId: $event->attendeeCheckinId,
                    eventType: $event->type,
                )->onQueue($queueName);
                break;
        }
    }
}
