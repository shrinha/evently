<?php

namespace Evently\Services\Domain\User;

use Evently\DomainObjects\UserDomainObject;
use Evently\Repository\Interfaces\AccountRepositoryInterface;
use Evently\Repository\Interfaces\AccountUserRepositoryInterface;
use Evently\Repository\Interfaces\UserRepositoryInterface;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class VerifyUserEmailService
{
    public function __construct(
        private readonly UserRepositoryInterface        $userRepository,
        private readonly AccountRepositoryInterface     $accountRepository,
        private readonly AccountUserRepositoryInterface $accountUserRepository,
    )
    {
    }

    public function markEmailAsVerified(UserDomainObject $user, int $accountId): void
    {
        $this->userRepository->updateWhere(
            attributes: [
                'email_verified_at' => now(),
            ],
            where: [
                'id' => $user->getId(),
            ],
        );

        $accountUser = $this->accountUserRepository->findFirstWhere(
            where: [
                'user_id' => $user->getId(),
                'account_id' => $accountId,
            ]
        );

        if ($accountUser === null) {
            throw new ResourceNotFoundException();
        }

        // If this is the account owner, mark the account as verified
        if ($accountUser->getIsAccountOwner()) {
            $this->accountRepository->updateWhere(
                attributes: [
                    'account_verified_at' => now(),
                ],
                where: [
                    'id' => $accountId,
                ]
            );
        }
    }
}
