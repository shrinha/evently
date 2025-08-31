<?php

namespace Evently\Http\Request\User;

use Evently\Http\Request\BaseRequest;

class EmailChangeRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'token' => 'required|string',
        ];
    }
}
