<?php

namespace Evently\Http\Actions\EventSettings;

use Evently\DomainObjects\EventDomainObject;
use Evently\Http\Actions\BaseAction;
use Evently\Http\Request\EventSettings\UpdateEventSettingsRequest;
use Evently\Resources\Event\EventSettingsResource;
use Evently\Services\Application\Handlers\EventSettings\DTO\UpdateEventSettingsDTO;
use Evently\Services\Application\Handlers\EventSettings\UpdateEventSettingsHandler;
use Illuminate\Http\JsonResponse;

class EditEventSettingsAction extends BaseAction
{
    public function __construct(
        private readonly UpdateEventSettingsHandler $updateEventSettingsHandler
    )
    {
    }

    public function __invoke(UpdateEventSettingsRequest $request, int $eventId): JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        $settings = array_merge(
            $request->validated(),
            [
                'event_id' => $eventId,
                'account_id' => $this->getAuthenticatedAccountId(),
            ],
        );

        $event = $this->updateEventSettingsHandler->handle(
            UpdateEventSettingsDTO::fromArray($settings),
        );

        return $this->resourceResponse(EventSettingsResource::class, $event);
    }
}
