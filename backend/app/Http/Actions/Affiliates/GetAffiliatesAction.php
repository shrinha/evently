<?php

declare(strict_types=1);

namespace Evently\Http\Actions\Affiliates;

use Evently\DomainObjects\AffiliateDomainObject;
use Evently\DomainObjects\EventDomainObject;
use Evently\Http\Actions\BaseAction;
use Evently\Http\DTO\QueryParamsDTO;
use Evently\Repository\Interfaces\AffiliateRepositoryInterface;
use Evently\Resources\Affiliate\AffiliateResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GetAffiliatesAction extends BaseAction
{
    public function __construct(private readonly AffiliateRepositoryInterface $affiliateRepository)
    {
    }

    public function __invoke(Request $request, int $eventId): JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        $affiliates = $this->affiliateRepository->findByEventId($eventId, QueryParamsDTO::fromArray($request->query->all()));

        return $this->filterableResourceResponse(
            resource: AffiliateResource::class,
            data: $affiliates,
            domainObject: AffiliateDomainObject::class
        );
    }
}
