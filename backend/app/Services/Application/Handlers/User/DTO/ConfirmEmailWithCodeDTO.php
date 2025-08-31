<?php

namespace Evently\Services\Application\Handlers\User\DTO;

use Evently\DataTransferObjects\BaseDataObject;

class ConfirmEmailWithCodeDTO extends BaseDataObject
{
    public string $code;
    public int $userId;
    public int $accountId;
}
