<?php

namespace Evently\Services\Application\Handlers\Organizer\DTO;

use Evently\DataTransferObjects\BaseDataObject;

class SendOrganizerContactMessageDTO extends BaseDataObject
{
    public function __construct(
        public int $organizer_id,
        public int $account_id,
        public string $name,
        public string $email,
        public string $message,
    ) {
    }
}
