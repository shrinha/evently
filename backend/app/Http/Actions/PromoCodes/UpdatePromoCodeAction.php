<?php

namespace Evently\Http\Actions\PromoCodes;

use Evently\DomainObjects\Enums\PromoCodeDiscountTypeEnum;
use Evently\DomainObjects\EventDomainObject;
use Evently\Exceptions\ResourceConflictException;
use Evently\Http\Actions\BaseAction;
use Evently\Http\Request\PromoCode\CreateUpdatePromoCodeRequest;
use Evently\Http\ResponseCodes;
use Evently\Resources\PromoCode\PromoCodeResource;
use Evently\Services\Application\Handlers\PromoCode\DTO\UpsertPromoCodeDTO;
use Evently\Services\Application\Handlers\PromoCode\UpdatePromoCodeHandler;
use Evently\Services\Domain\Product\Exception\UnrecognizedProductIdException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class UpdatePromoCodeAction extends BaseAction
{
    private UpdatePromoCodeHandler $updatePromoCodeHandler;

    public function __construct(UpdatePromoCodeHandler $promoCodeHandler)
    {
        $this->updatePromoCodeHandler = $promoCodeHandler;
    }

    /**
     * @throws ValidationException
     */
    public function __invoke(CreateUpdatePromoCodeRequest $request, int $eventId, int $promoCodeId): JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        try {
            $promoCode = $this->updatePromoCodeHandler->handle($promoCodeId, new UpsertPromoCodeDTO(
                code: strtolower($request->input('code')),
                event_id: $eventId,
                applicable_product_ids: $request->input('applicable_product_ids'),
                discount_type: PromoCodeDiscountTypeEnum::fromName($request->input('discount_type')),
                discount: $request->float('discount'),
                expiry_date: $request->input('expiry_date'),
                max_allowed_usages: $request->input('max_allowed_usages'),
            ));
        } catch (ResourceConflictException $e) {
            throw ValidationException::withMessages([
                'code' => $e->getMessage(),
            ]);
        } catch (UnrecognizedProductIdException $e) {
            throw ValidationException::withMessages([
                'applicable_product_ids' => $e->getMessage(),
            ]);
        }

        return $this->resourceResponse(
            resource: PromoCodeResource::class,
            data: $promoCode,
            statusCode: ResponseCodes::HTTP_CREATED
        );
    }
}
