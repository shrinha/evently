<?php

namespace Evently\Services\Application\Handlers\Order\DTO;

use Evently\DataTransferObjects\BaseDTO;

class EditOrderDTO extends BaseDTO
{
    public function __construct(
        public int     $id,
        public int     $eventId,
        public string  $firstName,
        public string  $lastName,
        public string  $email,
        public ?string $notes,
    )
    {
    }
}
