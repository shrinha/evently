<?php

namespace Evently\Http\Actions\Organizers;

use Evently\Http\Actions\BaseAction;
use Evently\Http\Request\Organizer\UpsertOrganizerRequest;
use Evently\Http\ResponseCodes;
use Evently\Resources\Organizer\OrganizerResource;
use Evently\Services\Application\Handlers\Organizer\CreateOrganizerHandler;
use Evently\Services\Application\Handlers\Organizer\DTO\CreateOrganizerDTO;
use Illuminate\Http\JsonResponse;

class CreateOrganizerAction extends BaseAction
{
    public function __construct(private readonly CreateOrganizerHandler $createOrganizerHandler)
    {
    }

    public function __invoke(UpsertOrganizerRequest $request): JsonResponse
    {
        $organizerData = array_merge(
            $request->validated(),
            [
                'account_id' => $this->getAuthenticatedAccountId(),
            ]
        );

        $organizer = $this->createOrganizerHandler->handle(
            organizerData: CreateOrganizerDTO::fromArray($organizerData),
        );

        return $this->resourceResponse(
            resource: OrganizerResource::class,
            data: $organizer,
            statusCode: ResponseCodes::HTTP_CREATED,
        );
    }
}
