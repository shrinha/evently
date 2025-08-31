<?php

namespace Evently\Services\Application\Handlers\Organizer;

use Evently\Repository\DTO\Organizer\OrganizerStatsResponseDTO;
use Evently\Repository\Interfaces\OrganizerRepositoryInterface;
use Evently\Services\Application\Handlers\Organizer\DTO\GetOrganizerStatsRequestDTO;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class GetOrganizerStatsHandler
{
    public function __construct(private readonly OrganizerRepositoryInterface $repository)
    {
    }

    public function handle(GetOrganizerStatsRequestDTO $statsRequestDTO): OrganizerStatsResponseDTO
    {
        $organizer = $this->repository->findFirstWhere([
            'id' => $statsRequestDTO->organizerId,
            'account_id' => $statsRequestDTO->accountId,
        ]);

        if ($organizer === null) {
            throw new ResourceNotFoundException('Organizer not found');
        }

        return $this->repository->getOrganizerStats(
            organizerId: $statsRequestDTO->organizerId,
            accountId: $statsRequestDTO->accountId,
            currencyCode: $statsRequestDTO->currencyCode ?? $organizer->getCurrency(),
        );
    }
}
