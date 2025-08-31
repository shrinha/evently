<?php

namespace Evently\Services\Application\Handlers\CheckInList\Public;

use Evently\DomainObjects\AttendeeDomainObject;
use Evently\DomainObjects\CheckInListDomainObject;
use Evently\DomainObjects\EventDomainObject;
use Evently\DomainObjects\Generated\CheckInListDomainObjectAbstract;
use Evently\DomainObjects\ProductDomainObject;
use Evently\Exceptions\CannotCheckInException;
use Evently\Helper\DateHelper;
use Evently\Repository\Eloquent\Value\Relationship;
use Evently\Repository\Interfaces\AttendeeRepositoryInterface;
use Evently\Repository\Interfaces\CheckInListRepositoryInterface;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class GetCheckInListAttendeePublicHandler
{
    public function __construct(
        private readonly AttendeeRepositoryInterface    $attendeeRepository,
        private readonly CheckInListRepositoryInterface $checkInListRepository,
    )
    {
    }

    /**
     * @throws CannotCheckInException
     */
    public function handle(string $shortId, string $attendeePublicId): AttendeeDomainObject
    {
        $checkInList = $this->checkInListRepository
            ->loadRelation(ProductDomainObject::class)
            ->loadRelation(new Relationship(EventDomainObject::class, name: 'event'))
            ->findFirstWhere([
                CheckInListDomainObjectAbstract::SHORT_ID => $shortId,
            ]);

        if (!$checkInList) {
            throw new ResourceNotFoundException(__('Check-in list not found'));
        }

        $this->validateCheckInListIsActive($checkInList);

        return $this->attendeeRepository->findFirstWhere([
            'public_id' => $attendeePublicId,
            'event_id' => $checkInList->getEventId(),
        ]);
    }

    /**
     * @todo - Move this to its own service. It's used 3 times
     * @throws CannotCheckInException
     */
    private function validateCheckInListIsActive(CheckInListDomainObject $checkInList): void
    {
        if ($checkInList->getExpiresAt() && DateHelper::utcDateIsPast($checkInList->getExpiresAt())) {
            throw new CannotCheckInException(__('Check-in list has expired'));
        }

        if ($checkInList->getActivatesAt() && DateHelper::utcDateIsFuture($checkInList->getActivatesAt())) {
            throw new CannotCheckInException(__('Check-in list is not active yet'));
        }
    }
}
