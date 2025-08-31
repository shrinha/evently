<?php

declare(strict_types=1);

namespace Evently\Repository\Interfaces;

use Evently\DomainObjects\OrganizerDomainObject;
use Evently\Repository\DTO\Organizer\OrganizerStatsResponseDTO;
use Evently\Repository\Eloquent\BaseRepository;

/**
 * @extends BaseRepository<OrganizerDomainObject>
 */
interface OrganizerRepositoryInterface extends RepositoryInterface
{
    public function getOrganizerStats(int $organizerId, int $accountId, string $currencyCode): OrganizerStatsResponseDTO;
}
