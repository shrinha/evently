<?php

namespace Evently\Services\Application\Handlers\User;

use Evently\Services\Application\Handlers\User\DTO\ConfirmEmailChangeDTO;
use Evently\Services\Domain\User\EmailConfirmationService;
use Evently\Services\Infrastructure\Encryption\Exception\DecryptionFailedException;
use Throwable;

readonly class ConfirmEmailAddressHandler
{
    public function __construct(
        private EmailConfirmationService $emailConfirmationService,
    )
    {
    }

    /**
     * @throws DecryptionFailedException|Throwable
     */
    public function handle(ConfirmEmailChangeDTO $data): void
    {
        $this->emailConfirmationService->confirmEmailAddress($data->token, $data->accountId);
    }
}
