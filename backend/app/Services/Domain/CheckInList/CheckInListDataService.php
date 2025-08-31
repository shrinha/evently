<?php

namespace Evently\Services\Domain\CheckInList;

use Exception;
use Evently\DomainObjects\AttendeeDomainObject;
use Evently\DomainObjects\CheckInListDomainObject;
use Evently\DomainObjects\Generated\AttendeeDomainObjectAbstract;
use Evently\DomainObjects\Generated\CheckInListDomainObjectAbstract;
use Evently\DomainObjects\ProductDomainObject;
use Evently\Exceptions\CannotCheckInException;
use Evently\Repository\Interfaces\AttendeeRepositoryInterface;
use Evently\Repository\Interfaces\CheckInListRepositoryInterface;
use Illuminate\Support\Collection;

class CheckInListDataService
{
    public function __construct(
        private readonly CheckInListRepositoryInterface $checkInListRepository,
        private readonly AttendeeRepositoryInterface    $attendeeRepository,
    )
    {
    }

    /**
     * @throws CannotCheckInException
     */
    public function verifyAttendeeBelongsToCheckInList(
        CheckInListDomainObject $checkInList,
        AttendeeDomainObject    $attendee,
    ): void
    {
        $allowedProductIds = $checkInList->getProducts()->map(fn($product) => $product->getId())->toArray() ?? [];

        if (!in_array($attendee->getProductId(), $allowedProductIds, true)) {
            throw new CannotCheckInException(
                __('Attendee :attendee_name is not allowed to check in using this check-in list', [
                    'attendee_name' => $attendee->getFullName(),
                ])
            );
        }
    }

    /**
     * @return Collection<AttendeeDomainObject>
     * @throws Exception
     *
     * @throws CannotCheckInException
     */
    public function getAttendees(Collection $attendeePublicIds): Collection
    {
        $attendeePublicIds = array_unique($attendeePublicIds->toArray());

        $attendees = $this->attendeeRepository->findWhereIn(
            field: AttendeeDomainObjectAbstract::PUBLIC_ID,
            values: $attendeePublicIds
        );

        if (count($attendees) !== count($attendeePublicIds)) {
            throw new CannotCheckInException(__('Invalid attendee code detected: :attendees ', [
                'attendees' => implode(', ', array_diff(
                        $attendeePublicIds,
                        $attendees->pluck(AttendeeDomainObjectAbstract::PUBLIC_ID)->toArray())
                ),
            ]));
        }

        return $attendees;
    }

    /**
     * @throws CannotCheckInException
     */
    public function getCheckInList(string $checkInListUuid): CheckInListDomainObject
    {
        $checkInList = $this->checkInListRepository
            ->loadRelation(ProductDomainObject::class)
            ->findFirstWhere([
                CheckInListDomainObjectAbstract::SHORT_ID => $checkInListUuid,
            ]);

        if ($checkInList === null) {
            throw new CannotCheckInException(__('Check-in list not found'));
        }

        return $checkInList;
    }
}
