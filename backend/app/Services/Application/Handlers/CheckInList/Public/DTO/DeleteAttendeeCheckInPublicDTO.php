<?php

namespace Evently\Services\Application\Handlers\CheckInList\Public\DTO;

use Evently\DataTransferObjects\BaseDTO;

class DeleteAttendeeCheckInPublicDTO extends BaseDTO
{
    public function __construct(
        public string $checkInListShortId,
        public string $checkInShortId,
        public string $checkInUserIpAddress,
    )
    {
    }
}
