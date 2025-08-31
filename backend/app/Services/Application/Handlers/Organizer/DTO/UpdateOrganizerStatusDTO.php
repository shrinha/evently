<?php

namespace Evently\Services\Application\Handlers\Organizer\DTO;

use Evently\DataTransferObjects\BaseDTO;

class UpdateOrganizerStatusDTO extends BaseDTO
{
    public function __construct(
        public string $status,
        public int $organizerId,
        public int $accountId,
    )
    {
    }
}