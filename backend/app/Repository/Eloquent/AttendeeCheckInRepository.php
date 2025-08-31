<?php

namespace Evently\Repository\Eloquent;

use Evently\DomainObjects\AttendeeCheckInDomainObject;
use Evently\Models\AttendeeCheckIn;
use Evently\Repository\Interfaces\AttendeeCheckInRepositoryInterface;

class AttendeeCheckInRepository extends BaseRepository implements AttendeeCheckInRepositoryInterface
{
    protected function getModel(): string
    {
        return AttendeeCheckIn::class;
    }

    public function getDomainObject(): string
    {
        return AttendeeCheckInDomainObject::class;
    }
}
