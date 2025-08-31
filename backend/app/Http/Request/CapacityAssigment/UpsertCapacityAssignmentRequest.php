<?php

namespace Evently\Http\Request\CapacityAssigment;

use Evently\DomainObjects\Status\CapacityAssignmentStatus;
use Evently\Http\Request\BaseRequest;
use Evently\Validators\Rules\RulesHelper;
use Illuminate\Validation\Rule;

class UpsertCapacityAssignmentRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'name' => RulesHelper::REQUIRED_STRING,
            'capacity' => ['nullable', 'numeric', 'min:1'],
            'status' => ['required', Rule::in(CapacityAssignmentStatus::valuesArray())],
            'product_ids' => ['required', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'product_ids.required' => __('Please select at least one product.'),
        ];
    }
}
