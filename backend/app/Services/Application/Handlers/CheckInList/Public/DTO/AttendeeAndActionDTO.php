<?php

namespace Evently\Services\Application\Handlers\CheckInList\Public\DTO;

use Evently\DomainObjects\Enums\AttendeeCheckInActionType;
use Spatie\LaravelData\Data;

class AttendeeAndActionDTO extends Data
{
    public function __construct(
        public string                    $public_id,
        public AttendeeCheckInActionType $action,
    )
    {
    }
}
