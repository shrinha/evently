<?php

namespace Evently\Services\Application\Handlers\PromoCode;

use Evently\DomainObjects\Enums\PromoCodeDiscountTypeEnum;
use Evently\DomainObjects\Generated\PromoCodeDomainObjectAbstract;
use Evently\DomainObjects\PromoCodeDomainObject;
use Evently\Exceptions\ResourceConflictException;
use Evently\Helper\DateHelper;
use Evently\Repository\Interfaces\EventRepositoryInterface;
use Evently\Repository\Interfaces\PromoCodeRepositoryInterface;
use Evently\Services\Application\Handlers\PromoCode\DTO\UpsertPromoCodeDTO;
use Evently\Services\Domain\Product\EventProductValidationService;
use Evently\Services\Domain\Product\Exception\UnrecognizedProductIdException;

readonly class UpdatePromoCodeHandler
{
    public function __construct(
        private PromoCodeRepositoryInterface  $promoCodeRepository,
        private EventProductValidationService $eventProductValidationService,
        private EventRepositoryInterface      $eventRepository,
    )
    {
    }

    /**
     * @throws ResourceConflictException
     * @throws UnrecognizedProductIdException
     */
    public function handle(int $promoCodeId, UpsertPromoCodeDTO $promoCodeDTO): PromoCodeDomainObject
    {
        $this->eventProductValidationService->validateProductIds(
            productIds: $promoCodeDTO->applicable_product_ids,
            eventId: $promoCodeDTO->event_id
        );

        $existing = $this->promoCodeRepository->findFirstWhere([
            PromoCodeDomainObjectAbstract::EVENT_ID => $promoCodeDTO->event_id,
            PromoCodeDomainObjectAbstract::CODE => $promoCodeDTO->code,
        ]);

        if ($existing !== null && $existing->getId() !== $promoCodeId) {
            throw new ResourceConflictException(
                __('The code :code is already in use for this event', ['code' => $promoCodeDTO->code])
            );
        }

        $event = $this->eventRepository->findById($promoCodeDTO->event_id);

        return $this->promoCodeRepository->updateFromArray($promoCodeId, [
            PromoCodeDomainObjectAbstract::CODE => $promoCodeDTO->code,
            PromoCodeDomainObjectAbstract::DISCOUNT => $promoCodeDTO->discount_type === PromoCodeDiscountTypeEnum::NONE
                ? 0.00
                : (float)$promoCodeDTO->discount,
            PromoCodeDomainObjectAbstract::DISCOUNT_TYPE => $promoCodeDTO->discount_type?->name,
            PromoCodeDomainObjectAbstract::EXPIRY_DATE => $promoCodeDTO->expiry_date
                ? DateHelper::convertToUTC($promoCodeDTO->expiry_date, $event->getTimezone())
                : null,
            PromoCodeDomainObjectAbstract::MAX_ALLOWED_USAGES => $promoCodeDTO->max_allowed_usages,
            PromoCodeDomainObjectAbstract::APPLICABLE_PRODUCT_IDS => $promoCodeDTO->applicable_product_ids,
        ]);
    }
}
