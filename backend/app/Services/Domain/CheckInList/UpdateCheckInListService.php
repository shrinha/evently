<?php

namespace Evently\Services\Domain\CheckInList;

use Evently\DomainObjects\CheckInListDomainObject;
use Evently\DomainObjects\Generated\CheckInListDomainObjectAbstract;
use Evently\Helper\DateHelper;
use Evently\Repository\Interfaces\CheckInListRepositoryInterface;
use Evently\Repository\Interfaces\EventRepositoryInterface;
use Evently\Services\Domain\Product\EventProductValidationService;
use Evently\Services\Domain\Product\Exception\UnrecognizedProductIdException;
use Illuminate\Database\DatabaseManager;

class UpdateCheckInListService
{
    public function __construct(
        private readonly DatabaseManager                     $databaseManager,
        private readonly EventProductValidationService       $eventProductValidationService,
        private readonly CheckInListProductAssociationService $checkInListProductAssociationService,
        private readonly CheckInListRepositoryInterface      $checkInListRepository,
        private readonly EventRepositoryInterface            $eventRepository,
    )
    {
    }

    /**
     * @throws UnrecognizedProductIdException
     */
    public function updateCheckInList(CheckInListDomainObject $checkInList, array $productIds): CheckInListDomainObject
    {
        return $this->databaseManager->transaction(function () use ($checkInList, $productIds) {
            $this->eventProductValidationService->validateProductIds($productIds, $checkInList->getEventId());
            $event = $this->eventRepository->findById($checkInList->getEventId());

            $this->checkInListRepository->updateWhere(
                attributes: [
                    CheckInListDomainObjectAbstract::NAME => $checkInList->getName(),
                    CheckInListDomainObjectAbstract::DESCRIPTION => $checkInList->getDescription(),
                    CheckInListDomainObjectAbstract::EVENT_ID => $checkInList->getEventId(),
                    CheckInListDomainObjectAbstract::EXPIRES_AT => $checkInList->getExpiresAt()
                        ? DateHelper::convertToUTC($checkInList->getExpiresAt(), $event->getTimezone())
                        : null,
                    CheckInListDomainObjectAbstract::ACTIVATES_AT => $checkInList->getActivatesAt()
                        ? DateHelper::convertToUTC($checkInList->getActivatesAt(), $event->getTimezone())
                        : null,
                ],
                where: [
                    CheckInListDomainObjectAbstract::ID => $checkInList->getId(),
                    CheckInListDomainObjectAbstract::EVENT_ID => $checkInList->getEventId(),
                ]
            );

            $this->checkInListProductAssociationService->addCheckInListToProducts(
                checkInListId: $checkInList->getId(),
                productIds: $productIds,
            );

            return $this->checkInListRepository->findFirstWhere(
                where: [
                    CheckInListDomainObjectAbstract::ID => $checkInList->getId(),
                    CheckInListDomainObjectAbstract::EVENT_ID => $checkInList->getEventId(),
                ]
            );
        });
    }
}
