<?php

declare(strict_types=1);

namespace Evently\Http\Request\Event;

use Evently\Http\Request\BaseRequest;
use Evently\Validators\EventRules;

class CreateEventRequest extends BaseRequest
{
    use EventRules;

    public function rules(): array
    {
        return $this->eventRules();
    }

    public function messages(): array
    {
        return $this->eventMessages();
    }
}
