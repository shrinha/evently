<?php

declare(strict_types=1);

namespace Evently\Http\Request\Account;

use Evently\Http\Request\BaseRequest;
use Evently\Locale;
use Evently\Validators\Rules\RulesHelper;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class CreateAccountRequest extends BaseRequest
{
    public function rules(): array
    {
        $currencies = include __DIR__ . '/../../../../data/currencies.php';

        return [
            'first_name' => RulesHelper::REQUIRED_STRING,
            'last_name' => RulesHelper::STRING,
            'email' => RulesHelper::REQUIRED_EMAIL,
            'password' => ['required', 'confirmed', Password::min(8)],
            'timezone' => ['timezone:all'],
            'currency_code' => [Rule::in(array_values($currencies))],
            'locale' => ['nullable', Rule::in(Locale::getSupportedLocales())],
            'invite_token' => ['nullable', 'string'],
        ];
    }
}
