<?php

namespace Evently\Services\Application\Handlers\Webhook;

use Evently\Repository\Eloquent\Value\OrderAndDirection;
use Evently\Repository\Interfaces\WebhookRepositoryInterface;
use Illuminate\Support\Collection;

class GetWebhooksHandler
{
    public function __construct(
        private readonly WebhookRepositoryInterface $webhookRepository,
    )
    {
    }

    public function handler(int $accountId, int $eventId): Collection
    {
        return $this->webhookRepository->findWhere(
            where: [
                'account_id' => $accountId,
                'event_id' => $eventId,
            ],
            orderAndDirections: [
                new OrderAndDirection('id', OrderAndDirection::DIRECTION_DESC),
            ]
        );
    }
}
