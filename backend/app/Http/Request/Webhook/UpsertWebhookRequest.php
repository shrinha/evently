<?php

namespace Evently\Http\Request\Webhook;

use Evently\DomainObjects\Status\WebhookStatus;
use Evently\Http\Request\BaseRequest;
use Evently\Services\Infrastructure\DomainEvents\Enums\DomainEventType;
use Illuminate\Validation\Rule;

class UpsertWebhookRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'url' => 'required|url',
            'event_types.*' => ['required', Rule::in(DomainEventType::valuesArray())],
            'status' => ['nullable', Rule::in(WebhookStatus::valuesArray())],
        ];
    }
}
