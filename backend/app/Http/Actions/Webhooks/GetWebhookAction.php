<?php

namespace Evently\Http\Actions\Webhooks;

use Evently\DomainObjects\EventDomainObject;
use Evently\Http\Actions\BaseAction;
use Evently\Resources\Webhook\WebhookResource;
use Evently\Services\Application\Handlers\Webhook\GetWebhookHandler;
use Illuminate\Http\JsonResponse;

class GetWebhookAction extends BaseAction
{
    public function __construct(
        private readonly GetWebhookHandler $getWebhookHandler,
    )
    {
    }

    public function __invoke(int $eventId, int $webhookId): JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        $webhook = $this->getWebhookHandler->handle(
            eventId: $eventId,
            webhookId: $webhookId
        );

        return $this->resourceResponse(
            resource: WebhookResource::class,
            data: $webhook
        );
    }
}
