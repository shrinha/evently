<?php

namespace Evently\Services\Application\Handlers\Auth;

use Evently\Repository\Interfaces\AccountUserRepositoryInterface;
use Evently\Services\Application\Handlers\Auth\DTO\LoginCredentialsDTO;
use Evently\Services\Domain\Auth\DTO\LoginResponse;
use Evently\Services\Domain\Auth\LoginService;

readonly class LoginHandler
{
    public function __construct(
        private LoginService                   $loginService,
        private AccountUserRepositoryInterface $accountUserRepository,
    )
    {
    }

    public function handle(LoginCredentialsDTO $loginCredentials): LoginResponse
    {
        $loginResponse = $this->loginService->authenticate(
            email: $loginCredentials->email,
            password: $loginCredentials->password,
            requestedAccountId: $loginCredentials->accountId,
        );

        if ($loginResponse->accountId !== null) {
            $this->accountUserRepository->updateWhere(
                attributes: [
                    'last_login_at' => now(),
                ],
                where: [
                    'user_id' => $loginResponse->user->getId(),
                    'account_id' => $loginResponse->accountId,
                ],
            );
        }

        return $loginResponse;
    }
}
