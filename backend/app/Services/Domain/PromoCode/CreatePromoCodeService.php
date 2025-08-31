<?php

namespace Evently\Services\Domain\PromoCode;

use Evently\DomainObjects\Enums\PromoCodeDiscountTypeEnum;
use Evently\DomainObjects\Generated\PromoCodeDomainObjectAbstract;
use Evently\DomainObjects\PromoCodeDomainObject;
use Evently\Exceptions\ResourceConflictException;
use Evently\Helper\DateHelper;
use Evently\Repository\Interfaces\EventRepositoryInterface;
use Evently\Repository\Interfaces\PromoCodeRepositoryInterface;
use Evently\Services\Domain\Product\EventProductValidationService;
use Evently\Services\Domain\Product\Exception\UnrecognizedProductIdException;

class CreatePromoCodeService
{
    public function __construct(
        private readonly PromoCodeRepositoryInterface  $promoCodeRepository,
        private readonly EventProductValidationService $eventProductValidationService,
        private readonly EventRepositoryInterface      $eventRepository,
    )
    {
    }

    /**
     * @throws ResourceConflictException
     * @throws UnrecognizedProductIdException
     */
    public function createPromoCode(PromoCodeDomainObject $promoCode): PromoCodeDomainObject
    {
        $this->checkForDuplicateCode($promoCode);

        if (!empty($promoCode->getApplicableProductIds())) {
            $this->eventProductValidationService->validateProductIds(
                productIds: $promoCode->getApplicableProductIds(),
                eventId: $promoCode->getEventId()
            );
        }

        $event = $this->eventRepository->findById($promoCode->getEventId());

        return $this->promoCodeRepository->create([
            PromoCodeDomainObjectAbstract::EVENT_ID => $promoCode->getEventId(),
            PromoCodeDomainObjectAbstract::CODE => $promoCode->getCode(),
            PromoCodeDomainObjectAbstract::DISCOUNT => $promoCode->getDiscountType() === PromoCodeDiscountTypeEnum::NONE->name
                ? 0.00
                : $promoCode->getDiscount(),
            PromoCodeDomainObjectAbstract::DISCOUNT_TYPE => $promoCode->getDiscountType(),
            PromoCodeDomainObjectAbstract::EXPIRY_DATE => $promoCode->getExpiryDate()
                ? DateHelper::convertToUTC($promoCode->getExpiryDate(), $event->getTimezone())
                : null,
            PromoCodeDomainObjectAbstract::MAX_ALLOWED_USAGES => $promoCode->getMaxAllowedUsages(),
            PromoCodeDomainObjectAbstract::APPLICABLE_PRODUCT_IDS => $promoCode->getApplicableProductIds(),
        ]);
    }

    /**
     * @throws ResourceConflictException
     */
    private function checkForDuplicateCode(PromoCodeDomainObject $promoCode): void
    {
        $existingPromoCode = $this->promoCodeRepository->findFirstWhere([
            PromoCodeDomainObjectAbstract::EVENT_ID => $promoCode->getEventId(),
            PromoCodeDomainObjectAbstract::CODE => $promoCode->getCode(),
        ]);

        if ($existingPromoCode !== null) {
            throw new ResourceConflictException(
                __('Promo code :code already exists', ['code' => $promoCode->getCode()]),
            );
        }
    }
}
