<?php

declare(strict_types=1);

namespace Evently\Repository\Interfaces;

use Evently\DomainObjects\AccountDomainObject;
use Evently\Repository\Eloquent\BaseRepository;

/**
 * @extends BaseRepository<AccountDomainObject>
 */
interface AccountRepositoryInterface extends RepositoryInterface
{
    public function findByEventId(int $eventId): AccountDomainObject;
}
