<?php

namespace Evently\Services\Application\Handlers\Organizer\DTO;

use Evently\DataTransferObjects\BaseDTO;
use Evently\Http\DTO\QueryParamsDTO;

class GetOrganizerEventsDTO extends BaseDTO
{
    public function __construct(
        public int            $organizerId,
        public int            $accountId,
        public QueryParamsDTO $queryParams
    )
    {
    }
}
