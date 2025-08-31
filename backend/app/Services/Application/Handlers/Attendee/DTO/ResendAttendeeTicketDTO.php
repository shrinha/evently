<?php

namespace Evently\Services\Application\Handlers\Attendee\DTO;

use Evently\DataTransferObjects\BaseDTO;

class ResendAttendeeTicketDTO extends BaseDTO
{
    public function __construct(
        public int $attendeeId,
        public int $eventId,
    )
    {
    }
}
