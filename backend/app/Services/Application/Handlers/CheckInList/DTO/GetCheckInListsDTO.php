<?php

namespace Evently\Services\Application\Handlers\CheckInList\DTO;

use Evently\DataTransferObjects\BaseDTO;
use Evently\Http\DTO\QueryParamsDTO;

class GetCheckInListsDTO extends BaseDTO
{
    public function __construct(
        public int            $eventId,
        public QueryParamsDTO $queryParams,
    )
    {
    }
}
