<?php

namespace Evently\Services\Application\Handlers\Webhook\DTO;

use Evently\DataTransferObjects\BaseDTO;
use Evently\DomainObjects\Status\WebhookStatus;

class CreateWebhookDTO extends BaseDTO
{
    public function __construct(
        public string        $url,
        public array         $eventTypes,
        public int           $eventId,
        public int           $userId,
        public int           $accountId,
        public WebhookStatus $status,
    )
    {
    }
}
