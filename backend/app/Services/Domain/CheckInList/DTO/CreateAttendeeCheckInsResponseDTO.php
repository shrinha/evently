<?php

namespace Evently\Services\Domain\CheckInList\DTO;

use Evently\DataTransferObjects\BaseDTO;
use Evently\DataTransferObjects\ErrorBagDTO;
use Illuminate\Support\Collection;

class CreateAttendeeCheckInsResponseDTO extends BaseDTO
{
    public function __construct(
        public Collection  $attendeeCheckIns,
        public ErrorBagDTO $errors,
    )
    {
    }
}
