<?php

namespace Evently\Services\Application\Handlers\CheckInList\Public;

use Evently\DomainObjects\CheckInListDomainObject;
use Evently\DomainObjects\EventDomainObject;
use Evently\DomainObjects\EventSettingDomainObject;
use Evently\DomainObjects\ProductDomainObject;
use Evently\Repository\Eloquent\Value\Relationship;
use Evently\Repository\Interfaces\CheckInListRepositoryInterface;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class GetCheckInListPublicHandler
{
    public function __construct(
        private readonly CheckInListRepositoryInterface $checkInListRepository,
    )
    {
    }

    public function handle(string $shortId): CheckInListDomainObject
    {
        $checkInList = $this->checkInListRepository
            ->loadRelation((new Relationship(domainObject: EventDomainObject::class, nested: [
                new Relationship(domainObject: EventSettingDomainObject::class, name: 'event_settings'),
            ], name: 'event')))
            ->loadRelation(ProductDomainObject::class)
            ->findFirstWhere([
                'short_id' => $shortId,
            ]);

        if ($checkInList === null) {
            throw new ResourceNotFoundException('Check-in list not found');
        }

        $attendeeCheckInCount = $this->checkInListRepository->getCheckedInAttendeeCountById($checkInList->getId());

        $checkInList->setCheckedInCount($attendeeCheckInCount->checkedInCount ?? 0);
        $checkInList->setTotalAttendeesCount($attendeeCheckInCount->totalAttendeesCount ?? 0);

        return $checkInList;
    }
}
