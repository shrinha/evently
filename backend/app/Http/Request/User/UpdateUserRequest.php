<?php

namespace Evently\Http\Request\User;

use Evently\DomainObjects\Enums\Role;
use Evently\DomainObjects\Status\UserStatus;
use Evently\Http\Request\BaseRequest;
use Evently\Validators\Rules\RulesHelper;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'first_name' => RulesHelper::STRING,
            'last_name' => RulesHelper::STRING,
            'status' => Rule::in([UserStatus::INACTIVE->name, UserStatus::ACTIVE->name]), // don't allow INVITED
            'role' => Rule::in(Role::valuesArray())
        ];
    }
}
