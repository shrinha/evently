<?php

namespace Evently\Services\Application\Handlers\User\DTO;

use Evently\DataTransferObjects\BaseDTO;

class ConfirmEmailChangeDTO extends BaseDTO
{
    public function __construct(
        public string $token,
        public int $accountId,
    )
    {
    }
}
