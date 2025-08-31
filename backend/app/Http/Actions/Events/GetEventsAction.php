<?php

declare(strict_types=1);

namespace Evently\Http\Actions\Events;

use Evently\DomainObjects\Enums\Role;
use Evently\DomainObjects\EventDomainObject;
use Evently\Http\Actions\BaseAction;
use Evently\Resources\Event\EventResource;
use Evently\Services\Application\Handlers\Event\DTO\GetEventsDTO;
use Evently\Services\Application\Handlers\Event\GetEventsHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GetEventsAction extends BaseAction
{
    public function __construct(
        private readonly GetEventsHandler $getEventsHandler,
    )
    {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $this->minimumAllowedRole(Role::ORGANIZER);

        $events = $this->getEventsHandler->handle(
            GetEventsDTO::fromArray([
                'accountId' => $this->getAuthenticatedAccountId(),
                'queryParams' => $this->getPaginationQueryParams($request),
            ]),
        );

        return $this->filterableResourceResponse(
            resource: EventResource::class,
            data: $events,
            domainObject: EventDomainObject::class,
        );
    }
}
