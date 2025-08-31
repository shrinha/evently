<?php

namespace Evently\Http\Actions\Organizers;

use Evently\DomainObjects\ImageDomainObject;
use Evently\Http\Actions\BaseAction;
use Evently\Repository\Interfaces\OrganizerRepositoryInterface;
use Evently\Resources\Organizer\OrganizerResource;
use Illuminate\Http\JsonResponse;

class GetOrganizersAction extends BaseAction
{
    public function __construct(private readonly OrganizerRepositoryInterface $organizerRepository)
    {
    }

    public function __invoke(): JsonResponse
    {
        $organizers = $this->organizerRepository
            ->loadRelation(ImageDomainObject::class)
            ->findwhere([
                'account_id' => $this->getAuthenticatedAccountId(),
            ]);

        return $this->resourceResponse(
            resource: OrganizerResource::class,
            data: $organizers,
        );
    }
}
