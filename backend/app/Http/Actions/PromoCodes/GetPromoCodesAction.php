<?php

namespace Evently\Http\Actions\PromoCodes;

use Evently\DomainObjects\EventDomainObject;
use Evently\DomainObjects\PromoCodeDomainObject;
use Evently\Http\Actions\BaseAction;
use Evently\Http\DTO\QueryParamsDTO;
use Evently\Repository\Interfaces\PromoCodeRepositoryInterface;
use Evently\Resources\PromoCode\PromoCodeResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GetPromoCodesAction extends BaseAction
{
    private PromoCodeRepositoryInterface $promoCodeRepository;

    public function __construct(PromoCodeRepositoryInterface $promoCodeRepository)
    {
        $this->promoCodeRepository = $promoCodeRepository;
    }

    public function __invoke(Request $request, int $eventId): JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        $codes = $this->promoCodeRepository->findByEventId($eventId, QueryParamsDTO::fromArray($request->query->all()));

        return $this->filterableResourceResponse(
            resource: PromoCodeResource::class,
            data: $codes,
            domainObject: PromoCodeDomainObject::class
        );
    }
}
