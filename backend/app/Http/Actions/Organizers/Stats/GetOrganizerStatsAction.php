<?php

namespace Evently\Http\Actions\Organizers\Stats;

use Evently\DomainObjects\OrganizerDomainObject;
use Evently\Http\Actions\BaseAction;
use Evently\Services\Application\Handlers\Organizer\DTO\GetOrganizerStatsRequestDTO;
use Evently\Services\Application\Handlers\Organizer\GetOrganizerStatsHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GetOrganizerStatsAction extends BaseAction
{
    public function __construct(
        private readonly GetOrganizerStatsHandler $getOrganizerStatsHandler,
    )
    {
    }

    public function __invoke(Request $request, int $organizerId): JsonResponse
    {
        $this->isActionAuthorized($organizerId, OrganizerDomainObject::class);

        $organizerStats = $this->getOrganizerStatsHandler->handle(new GetOrganizerStatsRequestDTO(
            organizerId: $organizerId,
            accountId: $this->getAuthenticatedAccountId(),
            currencyCode: $request->get('currency_code'),
        ));

        return $this->jsonResponse(
            data: $organizerStats,
            wrapInData: true,
        );
    }
}
