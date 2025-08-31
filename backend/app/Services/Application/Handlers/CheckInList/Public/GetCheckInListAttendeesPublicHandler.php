<?php

namespace Evently\Services\Application\Handlers\CheckInList\Public;

use Evently\DomainObjects\CheckInListDomainObject;
use Evently\DomainObjects\EventDomainObject;
use Evently\DomainObjects\Generated\CheckInListDomainObjectAbstract;
use Evently\DomainObjects\ProductDomainObject;
use Evently\Exceptions\CannotCheckInException;
use Evently\Helper\DateHelper;
use Evently\Http\DTO\QueryParamsDTO;
use Evently\Repository\Eloquent\Value\Relationship;
use Evently\Repository\Interfaces\AttendeeRepositoryInterface;
use Evently\Repository\Interfaces\CheckInListRepositoryInterface;
use Illuminate\Contracts\Pagination\Paginator;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class GetCheckInListAttendeesPublicHandler
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
    public function handle(string $shortId, QueryParamsDTO $queryParams): Paginator
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

        return $this->attendeeRepository->getAttendeesByCheckInShortId($shortId, $queryParams);
    }

    /**
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
