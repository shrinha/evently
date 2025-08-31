<?php

namespace Evently\Http\Actions\Organizers;

use Evently\DomainObjects\ImageDomainObject;
use Evently\DomainObjects\OrganizerDomainObject;
use Evently\Http\Actions\BaseAction;
use Evently\Repository\Interfaces\OrganizerRepositoryInterface;
use Evently\Resources\Organizer\OrganizerResource;
use Symfony\Component\HttpFoundation\Response;

class GetOrganizerAction extends BaseAction
{
    public function __construct(private readonly OrganizerRepositoryInterface $organizerRepository)
    {
    }

    public function __invoke(int $organizerId): Response
    {
        $this->isActionAuthorized(
            entityId: $organizerId,
            entityType: OrganizerDomainObject::class,
        );

        $organizer = $this->organizerRepository
            ->loadRelation(ImageDomainObject::class)
            ->findFirstWhere([
                'id' => $organizerId,
                'account_id' => $this->getAuthenticatedAccountId(),
            ]);

        if ($organizer === null) {
            return $this->notFoundResponse();
        }

        return $this->resourceResponse(
            resource: OrganizerResource::class,
            data: $organizer,
        );
    }
}
