<?php

namespace Evently\Http\Actions\Webhooks;

use Evently\DomainObjects\EventDomainObject;
use Evently\Http\Actions\BaseAction;
use Evently\Resources\Webhook\WebhookResource;
use Evently\Services\Application\Handlers\Webhook\GetWebhooksHandler;
use Illuminate\Http\JsonResponse;

class GetWebhooksAction extends BaseAction
{
    public function __construct(
        private readonly GetWebhooksHandler $getWebhooksHandler,
    )
    {
    }

    public function __invoke(int $eventId): JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        $webhooks = $this->getWebhooksHandler->handler(
            accountId: $this->getAuthenticatedAccountId(),
            eventId: $eventId
        );

        return $this->resourceResponse(
            resource: WebhookResource::class,
            data: $webhooks
        );
    }
}
