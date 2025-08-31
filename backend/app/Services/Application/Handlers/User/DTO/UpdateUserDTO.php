<?php

namespace Evently\Services\Application\Handlers\User\DTO;

use Evently\DataTransferObjects\BaseDTO;
use Evently\DomainObjects\Enums\Role;
use Evently\DomainObjects\Status\UserStatus;

class UpdateUserDTO extends BaseDTO
{
    public function __construct(
        public readonly int        $id,
        public readonly int        $account_id,
        public readonly string     $first_name,
        public readonly string     $last_name,
        public readonly Role       $role,
        public readonly UserStatus $status,
        public readonly int        $updated_by_user_id,
    )
    {
    }
}
