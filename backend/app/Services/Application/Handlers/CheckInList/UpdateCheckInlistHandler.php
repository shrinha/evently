<?php

namespace Evently\Services\Application\Handlers\CheckInList;

use Evently\DomainObjects\CheckInListDomainObject;
use Evently\Services\Application\Handlers\CheckInList\DTO\UpsertCheckInListDTO;
use Evently\Services\Domain\CheckInList\UpdateCheckInListService;
use Evently\Services\Domain\Product\Exception\UnrecognizedProductIdException;

class UpdateCheckInlistHandler
{
    public function __construct(
        private readonly UpdateCheckInlistService $updateCheckInlistService,
    )
    {
    }

    /**
     * @throws UnrecognizedProductIdException
     */
    public function handle(UpsertCheckInListDTO $data): CheckInListDomainObject
    {
        $checkInList = (new CheckInListDomainObject())
            ->setId($data->id)
            ->setName($data->name)
            ->setDescription($data->description)
            ->setEventId($data->eventId)
            ->setExpiresAt($data->expiresAt)
            ->setActivatesAt($data->activatesAt);

        return $this->updateCheckInlistService->updateCheckInlist(
            checkInList: $checkInList,
            productIds: $data->productIds
        );
    }
}
