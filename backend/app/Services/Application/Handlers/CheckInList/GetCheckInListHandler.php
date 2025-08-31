<?php

namespace Evently\Services\Application\Handlers\CheckInList;

use Evently\DomainObjects\CheckInListDomainObject;
use Evently\DomainObjects\EventDomainObject;
use Evently\DomainObjects\ProductDomainObject;
use Evently\Repository\Eloquent\Value\Relationship;
use Evently\Repository\Interfaces\CheckInListRepositoryInterface;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class GetCheckInListHandler
{
    public function __construct(
        private readonly CheckInListRepositoryInterface $checkInListRepository,
    )
    {
    }

    public function handle(int $checkInListId, int $eventId): CheckInListDomainObject
    {
        $checkInList = $this->checkInListRepository
            ->loadRelation(ProductDomainObject::class)
            ->loadRelation(new Relationship(domainObject: EventDomainObject::class, name: 'event'))
            ->findFirstWhere([
                'event_id' => $eventId,
                'id' => $checkInListId,
            ]);

        if ($checkInList === null) {
            throw new ResourceNotFoundException(__('Check-in list not found'));
        }

        $attendeeCheckInCount = $this->checkInListRepository->getCheckedInAttendeeCountById($checkInList->getId());

        $checkInList->setCheckedInCount($attendeeCheckInCount->checkedInCount ?? 0);
        $checkInList->setTotalAttendeesCount($attendeeCheckInCount->totalAttendeesCount ?? 0);

        return $checkInList;
    }
}
