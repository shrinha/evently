<?php

namespace Evently\Http\Actions\PromoCodes;

use Evently\DomainObjects\EventDomainObject;
use Evently\Http\Actions\BaseAction;
use Evently\Services\Application\Handlers\PromoCode\DeletePromoCodeHandler;
use Evently\Services\Application\Handlers\PromoCode\DTO\DeletePromoCodeDTO;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DeletePromoCodeAction extends BaseAction
{
    public function __construct(
        private readonly DeletePromoCodeHandler $deletePromoCodeHandler
    )
    {
    }

    public function __invoke(Request $request, int $eventId, int $promoCodeId): Response
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        $this->deletePromoCodeHandler->handle(DeletePromoCodeDTO::fromArray([
            'promo_code_id' => $promoCodeId,
            'event_id' => $eventId,
            'user_id' => $request->user()->id,
        ]));

        return $this->noContentResponse();
    }
}
