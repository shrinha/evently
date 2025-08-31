<?php

declare(strict_types=1);

namespace Evently\Repository\Eloquent;

use Evently\DomainObjects\AccountDomainObject;
use Evently\Models\Account;
use Evently\Repository\Interfaces\AccountRepositoryInterface;

class AccountRepository extends BaseRepository implements AccountRepositoryInterface
{
    protected function getModel(): string
    {
        return Account::class;
    }

    public function getDomainObject(): string
    {
        return AccountDomainObject::class;
    }

    public function findByEventId(int $eventId): AccountDomainObject
    {
        $account = $this
            ->model
            ->join('events', 'accounts.id', '=', 'events.account_id')
            ->where('events.id', $eventId)
            ->first();

        return $this->handleSingleResult($account, AccountDomainObject::class);
    }
}
