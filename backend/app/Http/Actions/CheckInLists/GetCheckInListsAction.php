<?php

namespace Evently\Http\Actions\CheckInLists;

use Evently\DomainObjects\CheckInListDomainObject;
use Evently\DomainObjects\EventDomainObject;
use Evently\Http\Actions\BaseAction;
use Evently\Resources\CheckInList\CheckInListResource;
use Evently\Services\Application\Handlers\CheckInList\DTO\GetCheckInListsDTO;
use Evently\Services\Application\Handlers\CheckInList\GetCheckInListsHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GetCheckInListsAction extends BaseAction
{
    public function __construct(
        private readonly GetCheckInListsHandler $getCheckInListsHandler,
    )
    {
    }

    public function __invoke(int $eventId, Request $request): JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        return $this->filterableResourceResponse(
            resource: CheckInListResource::class,
            data: $this->getCheckInListsHandler->handle(
                GetCheckInListsDTO::fromArray([
                    'eventId' => $eventId,
                    'queryParams' => $this->getPaginationQueryParams($request),
                ]),
            ),
            domainObject: CheckInListDomainObject::class,
        );
    }
}
