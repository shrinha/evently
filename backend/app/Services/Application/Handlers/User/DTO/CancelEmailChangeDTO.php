<?php

namespace Evently\Services\Application\Handlers\User\DTO;

use Evently\DataTransferObjects\BaseDTO;

class CancelEmailChangeDTO extends BaseDTO
{
    public function __construct(
        public int $userId,
        public int $accountId,
    )
    {
    }
}
