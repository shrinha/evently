<?php

namespace Evently\Http\Request\CheckInList;

use Evently\DomainObjects\Enums\AttendeeCheckInActionType;
use Evently\Http\Request\BaseRequest;
use Illuminate\Validation\Rule;

class CreateAttendeeCheckInPublicRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'attendees' => ['required', 'array'],
            'attendees.*.public_id' => ['required', 'string'],
            'attendees.*.action' => ['required', 'string', Rule::in(AttendeeCheckInActionType::valuesArray())],
        ];
    }
}
