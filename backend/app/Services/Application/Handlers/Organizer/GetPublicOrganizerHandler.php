<?php

namespace Evently\Services\Application\Handlers\Organizer;

use Evently\DomainObjects\ImageDomainObject;
use Evently\DomainObjects\OrganizerSettingDomainObject;
use Evently\Repository\Interfaces\OrganizerRepositoryInterface;

class GetPublicOrganizerHandler
{
    public function __construct(
        private readonly OrganizerRepositoryInterface $organizerRepository
    )
    {
    }

    public function handle(int $organizerId)
    {
        return $this->organizerRepository
            ->loadRelation(ImageDomainObject::class)
            ->loadRelation(OrganizerSettingDomainObject::class)
            ->findById($organizerId);
    }
}
