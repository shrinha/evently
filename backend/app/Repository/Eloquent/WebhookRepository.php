<?php

namespace Evently\Repository\Eloquent;

use Evently\DomainObjects\WebhookDomainObject;
use Evently\Models\Webhook;
use Evently\Repository\Interfaces\WebhookRepositoryInterface;

class WebhookRepository extends BaseRepository implements WebhookRepositoryInterface
{
    protected function getModel(): string
    {
        return Webhook::class;
    }

    public function getDomainObject(): string
    {
        return WebhookDomainObject::class;
    }
}
