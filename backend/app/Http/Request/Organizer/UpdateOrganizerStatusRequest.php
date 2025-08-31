<?php

namespace Evently\Http\Request\Organizer;

use Evently\DomainObjects\Status\OrganizerStatus;
use Evently\Http\Request\BaseRequest;
use Illuminate\Validation\Rule;

class UpdateOrganizerStatusRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(OrganizerStatus::valuesArray())],
        ];
    }
}