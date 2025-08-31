<?php

namespace Evently\Services\Application\Handlers\User;

use Evently\DomainObjects\AccountUserDomainObject;
use Evently\DomainObjects\Enums\Role;
use Evently\DomainObjects\Status\UserStatus;
use Evently\DomainObjects\UserDomainObject;
use Evently\Exceptions\CannotUpdateResourceException;
use Evently\Repository\Interfaces\AccountUserRepositoryInterface;
use Evently\Repository\Interfaces\UserRepositoryInterface;
use Evently\Services\Application\Handlers\User\DTO\UpdateUserDTO;
use Illuminate\Database\DatabaseManager;
use Psr\Log\LoggerInterface;
use Throwable;

readonly class UpdateUserHandler
{
    public function __construct(
        private UserRepositoryInterface        $userRepository,
        private LoggerInterface                $logger,
        private AccountUserRepositoryInterface $accountUserRepository,
        private DatabaseManager                $databaseManager,
    )
    {
    }

    /**
     * @throws CannotUpdateResourceException|Throwable
     */
    public function handle(UpdateUserDTO $updateUserData): UserDomainObject
    {
        return $this->databaseManager->transaction(function () use ($updateUserData) {
            return $this->updateUser($updateUserData);
        });
    }

    /**
     * @throws CannotUpdateResourceException
     */
    private function updateUser(UpdateUserDTO $updateUserData): UserDomainObject
    {
        /** @var AccountUserDomainObject $accountUser */
        $accountUser = $this->accountUserRepository->findFirstWhere(
            where: [
                'user_id' => $updateUserData->id,
                'account_id' => $updateUserData->account_id,
            ]
        );

        if ($updateUserData->role !== Role::ADMIN && $accountUser->getIsAccountOwner()) {
            throw new CannotUpdateResourceException(__(
                'You cannot update the role of the account owner'
            ));
        }

        if ($updateUserData->status !== UserStatus::ACTIVE && $accountUser->getIsAccountOwner()) {
            throw new CannotUpdateResourceException(__(
                'You cannot update the status of the account owner'
            ));
        }

        $this->userRepository->updateWhere(
            attributes: [
                'first_name' => $updateUserData->first_name,
                'last_name' => $updateUserData->last_name,
            ],
            where: [
                'id' => $updateUserData->id,
            ]
        );

        $this->accountUserRepository->updateWhere(
            attributes: [
                'role' => $updateUserData->role->name,
                'status' => $updateUserData->status->name,
            ],
            where: [
                'user_id' => $updateUserData->id,
                'account_id' => $updateUserData->account_id,
            ]
        );

        $this->logger->info('User updated', [
            'id' => $updateUserData->id,
            'updated_by_user_id' => $updateUserData->updated_by_user_id,
        ]);

        return $this->userRepository->findByIdAndAccountId(
            userId: $updateUserData->id,
            accountId: $updateUserData->account_id
        );
    }
}
