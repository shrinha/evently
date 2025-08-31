<?php

namespace Evently\Http\Actions\CapacityAssignments;

use Evently\DomainObjects\EventDomainObject;
use Evently\Http\Actions\BaseAction;
use Evently\Services\Application\Handlers\CapacityAssignment\DeleteCapacityAssignmentHandler;
use Illuminate\Http\Response;

class DeleteCapacityAssignmentAction extends BaseAction
{
    public function __construct(
        private readonly DeleteCapacityAssignmentHandler $deleteCapacityAssignmentHandler,
    )
    {
    }

    public function __invoke(int $eventId, int $capacityAssignmentId): Response
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        $this->deleteCapacityAssignmentHandler->handle(
            $capacityAssignmentId,
            $eventId,
        );

        return $this->noContentResponse();
    }
}
