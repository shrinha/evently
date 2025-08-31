<?php

namespace Evently\Http\Actions\Webhooks;

use Evently\DomainObjects\EventDomainObject;
use Evently\DomainObjects\WebhookLogDomainObject;
use Evently\Http\Actions\BaseAction;
use Evently\Resources\Webhook\WebhookLogResource;
use Evently\Services\Application\Handlers\Webhook\GetWebhookLogsHandler;
use Illuminate\Http\JsonResponse;

class GetWebhookLogsAction extends BaseAction
{
    public function __construct(
        private readonly GetWebhookLogsHandler $getWebhookLogsHandler,
    )
    {
    }

    public function __invoke(int $eventId, int $webhookId): JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        $webhookLogs = $this->getWebhookLogsHandler->handle(
            eventId: $eventId,
            webhookId: $webhookId,
        );

        $webhookLogs = $webhookLogs->sortBy(function (WebhookLogDomainObject $webhookLog) {
            return $webhookLog->getId();
        }, SORT_REGULAR, true);

        return $this->resourceResponse(
            resource: WebhookLogResource::class,
            data: $webhookLogs
        );
    }
}
