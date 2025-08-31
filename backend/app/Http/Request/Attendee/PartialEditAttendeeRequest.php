<?php

namespace Evently\Http\Request\Attendee;

use Evently\DomainObjects\Status\AttendeeStatus;
use Evently\Http\Request\BaseRequest;
use Evently\Validators\Rules\InsensitiveIn;

class PartialEditAttendeeRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'status' => ['sometimes', new InsensitiveIn(AttendeeStatus::valuesArray())],
            'first_name' => ['sometimes', 'string', 'max:100', 'min:1'],
            'last_name' => ['sometimes', 'string', 'max:100', 'min:1'],
            'email' => ['sometimes', 'email', 'max:100'],
        ];
    }
}
