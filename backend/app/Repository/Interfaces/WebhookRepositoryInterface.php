<?php

namespace Evently\Repository\Interfaces;

use Evently\DomainObjects\WebhookDomainObject;
use Evently\Repository\Eloquent\BaseRepository;

/**
 * @extends BaseRepository<WebhookDomainObject>
 */
interface WebhookRepositoryInterface extends RepositoryInterface
{
}
