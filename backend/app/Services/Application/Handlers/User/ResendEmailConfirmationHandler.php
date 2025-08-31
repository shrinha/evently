<?php

namespace Evently\Services\Application\Handlers\User;

use Evently\DomainObjects\UserDomainObject;
use Evently\Services\Domain\User\EmailConfirmationService;

class ResendEmailConfirmationHandler
{
    public function __construct(
        private readonly EmailConfirmationService $emailConfirmationService,
    )
    {
    }

    public function handle(UserDomainObject $user, int $accountId): void
    {
        $this->emailConfirmationService->sendConfirmation($user, $accountId);
    }
}
