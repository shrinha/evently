<?php

namespace Evently\Services\Domain\Account;

use Evently\DomainObjects\AccountDomainObject;
use Evently\DomainObjects\AccountUserDomainObject;
use Evently\DomainObjects\Enums\Role;
use Evently\DomainObjects\Status\UserStatus;
use Evently\DomainObjects\UserDomainObject;
use Evently\Repository\Interfaces\AccountUserRepositoryInterface;

readonly class AccountUserAssociationService
{
    public function __construct(
        private AccountUserRepositoryInterface $accountUserRepository,
    )
    {
    }

    public function associate(
        UserDomainObject    $user,
        AccountDomainObject $account,
        Role                $role,
        ?UserStatus         $status = null,
        ?int                $invitedByUserId = null,
        bool                $isAccountOwner = false,
    ): AccountUserDomainObject
    {
        $data = [
            'user_id' => $user->getId(),
            'account_id' => $account->getId(),
            'role' => $role->name,
            'is_account_owner' => $isAccountOwner,
        ];

        if ($status !== null) {
            $data['status'] = $status->name;
        }

        if ($invitedByUserId !== null) {
            $data['invited_by_user_id'] = $invitedByUserId;
        }

        return $this->accountUserRepository->create($data);
    }
}
