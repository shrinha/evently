<?php

namespace Evently\Services\Application\Handlers\CheckInList;

use Evently\DomainObjects\CheckInListDomainObject;
use Evently\Services\Application\Handlers\CheckInList\DTO\UpsertCheckInListDTO;
use Evently\Services\Domain\CheckInList\CreateCheckInListService;
use Evently\Services\Domain\Product\Exception\UnrecognizedProductIdException;

class CreateCheckInListHandler
{
    public function __construct(
        private readonly CreateCheckInListService $createCheckInListService,
    )
    {
    }

    /**
     * @throws UnrecognizedProductIdException
     */
    public function handle(UpsertCheckInListDTO $listData): CheckInListDomainObject
    {
        $checkInList = (new CheckInListDomainObject())
            ->setName($listData->name)
            ->setDescription($listData->description)
            ->setEventId($listData->eventId)
            ->setExpiresAt($listData->expiresAt)
            ->setActivatesAt($listData->activatesAt);

        return $this->createCheckInListService->createCheckInList(
            checkInList: $checkInList,
            productIds: $listData->productIds
        );
    }
}
