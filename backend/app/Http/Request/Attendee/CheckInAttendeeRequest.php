<?php

namespace Evently\Http\Request\Attendee;

use Evently\Http\Request\BaseRequest;

class CheckInAttendeeRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'action' => 'required|string|in:check_in,check_out',
        ];
    }
}
