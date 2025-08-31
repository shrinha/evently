<?php

declare(strict_types=1);

namespace Evently\Http\Request\Event;

use Evently\Http\Request\BaseRequest;
use Evently\Validators\EventRules;

class UpdateEventRequest extends BaseRequest
{
    use EventRules;

    public function rules(): array
    {
        $rules =  $this->eventRules();
        unset($rules['organizer_id']);

        return $rules;
    }

    public function messages(): array
    {
        return $this->eventMessages();
    }
}
