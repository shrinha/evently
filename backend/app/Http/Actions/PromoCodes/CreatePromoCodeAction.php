<?php

namespace Evently\Http\Actions\PromoCodes;

use Evently\DomainObjects\Enums\PromoCodeDiscountTypeEnum;
use Evently\DomainObjects\EventDomainObject;
use Evently\Exceptions\ResourceConflictException;
use Evently\Http\Actions\BaseAction;
use Evently\Http\Request\PromoCode\CreateUpdatePromoCodeRequest;
use Evently\Http\ResponseCodes;
use Evently\Resources\PromoCode\PromoCodeResource;
use Evently\Services\Application\Handlers\PromoCode\CreatePromoCodeHandler;
use Evently\Services\Application\Handlers\PromoCode\DTO\UpsertPromoCodeDTO;
use Evently\Services\Domain\Product\Exception\UnrecognizedProductIdException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class CreatePromoCodeAction extends BaseAction
{
    private CreatePromoCodeHandler $createPromoCodeHandler;

    public function __construct(CreatePromoCodeHandler $promoCodeHandler)
    {
        $this->createPromoCodeHandler = $promoCodeHandler;
    }

    /**
     * @throws ValidationException
     */
    public function __invoke(CreateUpdatePromoCodeRequest $request, int $eventId): JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        try {
            $promoCode = $this->createPromoCodeHandler->handle($eventId, new UpsertPromoCodeDTO(
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
            PromoCodeResource::class,
            $promoCode,
            ResponseCodes::HTTP_CREATED
        );
    }
}
