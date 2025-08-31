<?php

declare(strict_types=1);

namespace Evently\Http\Request\Order;

use Evently\Http\Request\BaseRequest;
use Evently\Validators\CompleteOrderValidator;

class CompleteOrderRequest extends BaseRequest
{
    public function rules(CompleteOrderValidator $orderValidator): array
    {
        return $orderValidator->rules();
    }

    public function messages(): array
    {
        return app(CompleteOrderValidator::class)->messages();
    }
}
