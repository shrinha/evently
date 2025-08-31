<?php

namespace Evently\Http\Actions\Webhooks;

use Evently\DomainObjects\EventDomainObject;
use Evently\Http\Actions\BaseAction;
use Evently\Services\Application\Handlers\Webhook\DeleteWebhookHandler;
use Illuminate\Http\Response;

class DeleteWebhookAction extends BaseAction
{
    public function __construct(
        private readonly DeleteWebhookHandler $deleteWebhookHandler,
    )
    {
    }

    public function __invoke(int $eventId, int $webhookId): Response
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        $this->deleteWebhookHandler->handle(
            $eventId,
            $webhookId,
        );

        return $this->deletedResponse();
    }
}
