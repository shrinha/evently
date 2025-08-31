<?php

namespace Evently\Http\Actions\Organizers;

use Evently\DomainObjects\EventDomainObject;
use Evently\DomainObjects\OrganizerDomainObject;
use Evently\Http\Actions\BaseAction;
use Evently\Http\DTO\QueryParamsDTO;
use Evently\Resources\Event\EventResource;
use Evently\Services\Application\Handlers\Organizer\DTO\GetOrganizerEventsDTO;
use Evently\Services\Application\Handlers\Organizer\GetOrganizerEventsHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GetOrganizerEventsAction extends BaseAction
{
    public function __construct(
        private readonly GetOrganizerEventsHandler $getOrganizerEventsHandler,
    )
    {
    }

    public function __invoke(int $organizerId, Request $request): JsonResponse
    {
        $this->isActionAuthorized(
            entityId: $organizerId,
            entityType: OrganizerDomainObject::class
        );

        $events = $this->getOrganizerEventsHandler->handle(new GetOrganizerEventsDTO(
            organizerId: $organizerId,
            accountId: $this->getAuthenticatedAccountId(),
            queryParams: QueryParamsDTO::fromArray($request->query->all())
        ));

        return $this->filterableResourceResponse(
            resource: EventResource::class,
            data: $events,
            domainObject: EventDomainObject::class
        );
    }
}
