<?php

namespace Evently\Services\Application\Handlers\Auth;

use Evently\DomainObjects\PasswordResetTokenDomainObject;
use Evently\Exceptions\InvalidPasswordResetTokenException;
use Evently\Services\Domain\Auth\ResetPasswordTokenValidateService;

class ValidateResetPasswordTokenHandler
{
    private ResetPasswordTokenValidateService $passwordTokenValidateService;

    public function __construct(ResetPasswordTokenValidateService $passwordTokenValidateService)
    {
        $this->passwordTokenValidateService = $passwordTokenValidateService;
    }

    /**
     * @throws InvalidPasswordResetTokenException
     */
    public function handle(string $token): PasswordResetTokenDomainObject
    {
        return $this->passwordTokenValidateService->validateAndFetchToken($token);
    }
}
