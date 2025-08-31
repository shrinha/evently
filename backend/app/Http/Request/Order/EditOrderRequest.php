<?php

namespace Evently\Http\Request\Order;

use Evently\Http\Request\BaseRequest;
use Evently\Validators\Rules\RulesHelper;

class EditOrderRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'email' => RulesHelper::REQUIRED_EMAIL,
            'first_name' => RulesHelper::REQUIRED_STRING,
            'last_name' => RulesHelper::REQUIRED_STRING,
            'notes' => RulesHelper::OPTIONAL_TEXT_MEDIUM_LENGTH,
        ];
    }
}
