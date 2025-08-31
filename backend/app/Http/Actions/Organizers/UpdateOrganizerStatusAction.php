<?php

namespace Evently\Http\Actions\Organizers;

use Evently\DomainObjects\OrganizerDomainObject;
use Evently\Exceptions\AccountNotVerifiedException;
use Evently\Http\Actions\BaseAction;
use Evently\Http\Request\Organizer\UpdateOrganizerStatusRequest;
use Evently\Http\ResponseCodes;
use Evently\Resources\Organizer\OrganizerResource;
use Evently\Services\Application\Handlers\Organizer\DTO\UpdateOrganizerStatusDTO;
use Evently\Services\Application\Handlers\Organizer\UpdateOrganizerStatusHandler;
use Illuminate\Http\JsonResponse;

class UpdateOrganizerStatusAction extends BaseAction
{
    public function __construct(
        private readonly UpdateOrganizerStatusHandler $updateOrganizerStatusHandler,
    )
    {
    }

    public function __invoke(UpdateOrganizerStatusRequest $request, int $organizerId): JsonResponse
    {
        $this->isActionAuthorized($organizerId, OrganizerDomainObject::class);

        try {
            $updatedOrganizer = $this->updateOrganizerStatusHandler->handle(UpdateOrganizerStatusDTO::fromArray([
                'status' => $request->input('status'),
                'organizerId' => $organizerId,
                'accountId' => $this->getAuthenticatedAccountId(),
            ]));
        } catch (AccountNotVerifiedException $e) {
            return $this->errorResponse($e->getMessage(), ResponseCodes::HTTP_UNPROCESSABLE_ENTITY);
        }

        return $this->resourceResponse(OrganizerResource::class, $updatedOrganizer);
    }
}
