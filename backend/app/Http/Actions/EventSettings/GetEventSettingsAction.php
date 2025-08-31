<?php

namespace Evently\Http\Actions\EventSettings;

use Evently\DomainObjects\EventDomainObject;
use Evently\Http\Actions\BaseAction;
use Evently\Repository\Interfaces\EventSettingsRepositoryInterface;
use Evently\Resources\Event\EventSettingsResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class GetEventSettingsAction extends BaseAction
{
    public function __construct(private readonly EventSettingsRepositoryInterface $eventSettingsRepository)
    {
    }

    public function __invoke(int $eventId): Response|JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        $settings = $this->eventSettingsRepository->findFirstWhere([
            'event_id' => $eventId
        ]);

        if ($settings === null) {
            return $this->notFoundResponse();
        }

        return $this->resourceResponse(EventSettingsResource::class, $settings);
    }
}
