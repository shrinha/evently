<?php

namespace Evently\Http\Request\Auth;

use Evently\Http\Request\BaseRequest;
use Evently\Validators\Rules\RulesHelper;

class AcceptInvitationRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'first_name' => RulesHelper::REQUIRED_STRING,
            'last_name' => RulesHelper::STRING,
            'password' => 'required|string|min:8|confirmed',
            'timezone' => ['required', 'timezone:all'],
        ];
    }
}
