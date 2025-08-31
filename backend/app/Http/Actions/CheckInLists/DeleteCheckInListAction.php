<?php

namespace Evently\Http\Actions\CheckInLists;

use Evently\DomainObjects\EventDomainObject;
use Evently\Http\Actions\BaseAction;
use Evently\Services\Application\Handlers\CheckInList\DeleteCheckInListHandler;
use Illuminate\Http\Response;

class DeleteCheckInListAction extends BaseAction
{
    public function __construct(
        private readonly DeleteCheckInListHandler $deleteCheckInListHandler,
    )
    {
    }

    public function __invoke(int $eventId, int $checkInListId): Response
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        $this->deleteCheckInListHandler->handle(
            eventId: $eventId,
            checkInListId: $checkInListId,
        );

        return $this->noContentResponse();
    }
}
