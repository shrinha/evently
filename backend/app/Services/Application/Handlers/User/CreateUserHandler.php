<?php

namespace Evently\Services\Application\Handlers\User;

use Evently\DomainObjects\AccountDomainObject;
use Evently\DomainObjects\Status\UserStatus;
use Evently\DomainObjects\UserDomainObject;
use Evently\Exceptions\ResourceConflictException;
use Evently\Repository\Interfaces\AccountRepositoryInterface;
use Evently\Repository\Interfaces\UserRepositoryInterface;
use Evently\Services\Application\Handlers\User\DTO\CreateUserDTO;
use Evently\Services\Domain\Account\AccountUserAssociationService;
use Evently\Services\Domain\User\SendUserInvitationService;
use Illuminate\Database\DatabaseManager;
use Throwable;

readonly class CreateUserHandler
{
    public function __construct(
        private UserRepositoryInterface       $userRepository,
        private AccountRepositoryInterface    $accountRepository,
        private SendUserInvitationService     $sendUserInvitationService,
        private AccountUserAssociationService $accountUserAssociationService,
        private DatabaseManager               $databaseManager,
    )
    {
    }

    /**
     * @throws ResourceConflictException
     * @throws Throwable
     */
    public function handle(CreateUserDTO $userData): UserDomainObject
    {
        return $this->databaseManager->transaction(function () use ($userData) {
            $existingUser = $this->getExistingUser($userData);

            $authenticatedAccount = $this->accountRepository->findById($userData->account_id);

            $invitedUser = $existingUser ?? $this->createUser($userData, $authenticatedAccount);

            $invitedUser->setCurrentAccountUser($this->accountUserAssociationService->associate(
                user: $invitedUser,
                account: $authenticatedAccount,
                role: $userData->role,
                status: UserStatus::INVITED,
                invitedByUserId: $userData->invited_by,
            ));

            $this->sendUserInvitationService->sendInvitation($invitedUser, $authenticatedAccount->getId());

            return $invitedUser;
        });

    }

    private function createUser(CreateUserDTO $userData, AccountDomainObject $authenticatedAccount): UserDomainObject
    {
        return $this->userRepository
            ->create([
                'first_name' => $userData->first_name,
                'last_name' => $userData->last_name,
                'email' => strtolower($userData->email),
                'password' => 'invited', // initially, a user is in an invited state, so they don't have a password
                'timezone' => $authenticatedAccount->getTimezone(),
            ]);
    }

    /**
     * @throws ResourceConflictException
     */
    private function getExistingUser(CreateUserDTO $userData): ?UserDomainObject
    {
        $existingUser = $this->userRepository
            ->loadRelation(AccountDomainObject::class)
            ->findFirstWhere([
                'email' => $userData->email,
            ]);

        if ($existingUser === null) {
            return null;
        }

        if ($existingUser->accounts->some(fn($account) => $account->getId() === $userData->account_id)) {
            throw new ResourceConflictException(
                __('The email :email already exists on this account', [
                    'email' => $userData->email,
                ])
            );
        }

        return $existingUser;
    }
}
