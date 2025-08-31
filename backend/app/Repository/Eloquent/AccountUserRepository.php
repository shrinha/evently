<?php

declare(strict_types=1);

namespace Evently\Repository\Eloquent;

use Evently\DomainObjects\AccountUserDomainObject;
use Evently\Models\AccountUser;
use Evently\Repository\Interfaces\AccountUserRepositoryInterface;

class AccountUserRepository extends BaseRepository implements AccountUserRepositoryInterface
{
    protected function getModel(): string
    {
        return AccountUser::class;
    }

    public function getDomainObject(): string
    {
        return AccountUserDomainObject::class;
    }
}
