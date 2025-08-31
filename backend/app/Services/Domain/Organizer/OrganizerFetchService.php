<?php

namespace Evently\Services\Domain\Organizer;

use Evently\DomainObjects\OrganizerDomainObject;
use Evently\Exceptions\OrganizerNotFoundException;
use Evently\Repository\Interfaces\OrganizerRepositoryInterface;

class OrganizerFetchService
{
    public function __construct(
        public readonly OrganizerRepositoryInterface $organizerRepository,
    )
    {
    }

    /**
     * @throws OrganizerNotFoundException
     */
    public function fetchOrganizer(int $organizerId, int $accountId): OrganizerDomainObject
    {
        $organizer = $this->organizerRepository->findFirstWhere([
            'id' => $organizerId,
            'account_id' => $accountId,
        ]);

        if ($organizer === null) {
            throw new OrganizerNotFoundException(
                __('Organizer :id not found', ['id' => $organizerId])
            );
        }

        return $organizer;
    }
}
