<?php

namespace Evently\Http\Request\Event;

use Evently\DomainObjects\Status\EventStatus;
use Evently\Http\Request\BaseRequest;
use Illuminate\Validation\Rule;

class UpdateEventStatusRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(EventStatus::valuesArray())],
        ];
    }
}
