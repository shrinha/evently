<?php

namespace Evently\Jobs\Order\Webhook;

use Evently\Services\Infrastructure\DomainEvents\Enums\DomainEventType;
use Evently\Services\Infrastructure\Webhook\WebhookDispatchService;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DispatchCheckInWebhookJob
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int             $attendeeCheckInId,
        public DomainEventType $eventType,
    )
    {
    }

    public function handle(WebhookDispatchService $webhookDispatchService): void
    {
        $webhookDispatchService->dispatchCheckInWebhook(
            eventType: $this->eventType,
            attendeeCheckInId: $this->attendeeCheckInId,
        );
    }
}
