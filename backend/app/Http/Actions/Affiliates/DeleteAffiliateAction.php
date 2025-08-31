<?php

declare(strict_types=1);

namespace Evently\Http\Actions\Affiliates;

use Evently\DomainObjects\EventDomainObject;
use Evently\Http\Actions\BaseAction;
use Evently\Services\Application\Handlers\Affiliate\DeleteAffiliateHandler;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DeleteAffiliateAction extends BaseAction
{
    public function __construct(
        private readonly DeleteAffiliateHandler $deleteAffiliateHandler
    )
    {
    }

    public function __invoke(Request $request, int $eventId, int $affiliateId): Response
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        $this->deleteAffiliateHandler->handle($affiliateId, $eventId);

        return $this->deletedResponse();
    }
}
