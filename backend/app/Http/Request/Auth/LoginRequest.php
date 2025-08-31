<?php

namespace Evently\Http\Request\Auth;

use Evently\Http\Request\BaseRequest;

class LoginRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'max:255'],
            'account_id' => ['integer', 'nullable'],
        ];
    }
}
