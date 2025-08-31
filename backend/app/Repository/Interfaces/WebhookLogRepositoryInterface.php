<?php

namespace Evently\Repository\Interfaces;

use Evently\DomainObjects\WebhookLogDomainObject;
use Evently\Repository\Eloquent\BaseRepository;

/**
 * @extends BaseRepository<WebhookLogDomainObject>
 */
interface WebhookLogRepositoryInterface extends RepositoryInterface
{
    public function deleteOldLogs(int $webhookId, int $numberToKeep = 20): void;
}
