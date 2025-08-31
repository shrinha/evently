<?php

namespace Evently\Http\Actions\PromoCodes;

use Evently\DomainObjects\EventDomainObject;
use Evently\Http\Actions\BaseAction;
use Evently\Repository\Interfaces\PromoCodeRepositoryInterface;
use Evently\Resources\PromoCode\PromoCodeResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GetPromoCodeAction extends BaseAction
{
    private PromoCodeRepositoryInterface $promoCodeRepository;

    public function __construct(PromoCodeRepositoryInterface $promoCodeRepository)
    {
        $this->promoCodeRepository = $promoCodeRepository;
    }

    public function __invoke(Request $request, int $eventId, int $promoCodeId): JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        $codes = $this->promoCodeRepository->findById($promoCodeId);

        return $this->resourceResponse(PromoCodeResource::class, $codes);
    }
}
