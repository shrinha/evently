<?php

declare(strict_types=1);

namespace Evently\Http\Actions\Affiliates;

use Evently\DomainObjects\EventDomainObject;
use Evently\DomainObjects\Status\AffiliateStatus;
use Evently\Http\Actions\BaseAction;
use Evently\Http\Request\Affiliate\UpdateAffiliateRequest;
use Evently\Resources\Affiliate\AffiliateResource;
use Evently\Services\Application\Handlers\Affiliate\DTO\UpsertAffiliateDTO;
use Evently\Services\Application\Handlers\Affiliate\UpdateAffiliateHandler;
use Illuminate\Http\JsonResponse;

class UpdateAffiliateAction extends BaseAction
{
    public function __construct(
        private readonly UpdateAffiliateHandler $updateAffiliateHandler
    )
    {
    }

    public function __invoke(UpdateAffiliateRequest $request, int $eventId, int $affiliateId): JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        $affiliate = $this->updateAffiliateHandler->handle(
            $affiliateId,
            $eventId,
            new UpsertAffiliateDTO(
                name: $request->input('name'),
                code: '', // Code cannot be updated
                email: $request->input('email'),
                status: AffiliateStatus::from($request->input('status')),
            )
        );

        return $this->resourceResponse(
            resource: AffiliateResource::class,
            data: $affiliate
        );
    }
}
