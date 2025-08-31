<?php

namespace Evently\Http\Actions\Organizers\Settings;

use Evently\DomainObjects\OrganizerDomainObject;
use Evently\Http\Actions\BaseAction;
use Evently\Repository\Interfaces\OrganizerSettingsRepositoryInterface;
use Evently\Resources\Organizer\OrganizerSettingsResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class GetOrganizerSettingsAction extends BaseAction
{
    public function __construct(private readonly OrganizerSettingsRepositoryInterface $settingsRepository)
    {
    }

    public function __invoke(int $organizerId): Response|JsonResponse
    {
        $this->isActionAuthorized($organizerId, OrganizerDomainObject::class);

        $settings = $this->settingsRepository->findFirstWhere([
            'organizer_id' => $organizerId
        ]);

        if ($settings === null) {
            return $this->notFoundResponse();
        }

        return $this->resourceResponse(OrganizerSettingsResource::class, $settings);
    }
}
