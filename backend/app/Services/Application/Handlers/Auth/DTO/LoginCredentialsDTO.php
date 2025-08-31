<?php

namespace Evently\Services\Application\Handlers\Auth\DTO;

use Evently\DataTransferObjects\BaseDTO;

class LoginCredentialsDTO extends BaseDTO
{
    public function __construct(
        public readonly string $email,
        public readonly string $password,
        public readonly ?int $accountId = null,
    )
    {
    }
}
