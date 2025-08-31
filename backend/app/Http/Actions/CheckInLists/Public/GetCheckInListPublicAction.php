<?php

namespace Evently\Http\Actions\CheckInLists\Public;

use Evently\Http\Actions\BaseAction;
use Evently\Resources\CheckInList\CheckInListResourcePublic;
use Evently\Services\Application\Handlers\CheckInList\Public\GetCheckInListPublicHandler;
use Illuminate\Http\JsonResponse;

class GetCheckInListPublicAction extends BaseAction
{
    public function __construct(
        private readonly GetCheckInListPublicHandler $getCheckInListPublicHandler,
    )
    {
    }

    public function __invoke(string $checkInListShortId): JsonResponse
    {
        $checkInList = $this->getCheckInListPublicHandler->handle($checkInListShortId);

        return $this->resourceResponse(
            resource: CheckInListResourcePublic::class,
            data: $checkInList,
        );
    }
}
