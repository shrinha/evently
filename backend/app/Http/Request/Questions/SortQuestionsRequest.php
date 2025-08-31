<?php

namespace Evently\Http\Request\Questions;

use Evently\Http\Request\BaseRequest;

class SortQuestionsRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            '*.id' => 'integer|required',
            '*.order' => 'integer|required',
        ];
    }
}
