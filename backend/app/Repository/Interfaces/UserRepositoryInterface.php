<?php

declare(strict_types=1);

namespace Evently\Repository\Interfaces;

use Evently\DomainObjects\UserDomainObject;
use Evently\Repository\Eloquent\BaseRepository;
use Illuminate\Support\Collection;

/**
 * @extends BaseRepository<UserDomainObject>
 */
interface UserRepositoryInterface extends RepositoryInterface
{
    public function findByIdAndAccountId(int $userId, int $accountId): UserDomainObject;

    public function findUsersByAccountId(int $accountId): ?Collection;
}
