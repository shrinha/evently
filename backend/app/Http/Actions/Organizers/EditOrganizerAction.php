<?php

namespace Evently\Http\Actions\Organizers;

use Evently\DomainObjects\OrganizerDomainObject;
use Evently\Http\Actions\BaseAction;
use Evently\Http\Request\Organizer\UpsertOrganizerRequest;
use Evently\Resources\Organizer\OrganizerResource;
use Evently\Services\Application\Handlers\Organizer\DTO\EditOrganizerDTO;
use Evently\Services\Application\Handlers\Organizer\EditOrganizerHandler;
use Illuminate\Http\JsonResponse;

class EditOrganizerAction extends BaseAction
{
    public function __construct(private readonly EditOrganizerHandler $editOrganizerHandler)
    {
    }

    public function __invoke(UpsertOrganizerRequest $request, int $organizerId): JsonResponse
    {
        $this->isActionAuthorized(
            entityId: $organizerId,
            entityType: OrganizerDomainObject::class,
        );

        $organizerData = array_merge(
            $request->validated(),
            [
                'id' => $organizerId,
                'account_id' => $this->getAuthenticatedAccountId(),
            ]
        );

        $organizer = $this->editOrganizerHandler->handle(
            organizerData: EditOrganizerDTO::from($organizerData),
        );

        return $this->resourceResponse(
            resource: OrganizerResource::class,
            data: $organizer,
        );
    }
}
