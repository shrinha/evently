<?php

declare(strict_types=1);

namespace Evently\Http\Request\Order;

use Evently\Http\Request\BaseRequest;
use Evently\Services\Domain\Order\OrderCreateRequestValidationService;

class CreateOrderRequest extends BaseRequest
{
    /**
     * @see OrderCreateRequestValidationService
     */
    public function rules(): array
    {
        return [];
    }
}
