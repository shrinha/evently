<?php

namespace Evently\Services\Application\Handlers\Event\DTO;

use Evently\DataTransferObjects\BaseDTO;
use Evently\Http\DTO\QueryParamsDTO;

class GetPublicOrganizerEventsDTO extends BaseDTO
{
    public function __construct(
        public int            $organizerId,
        public QueryParamsDTO $queryParams,
        public ?int           $authenticatedAccountId = null,
    )
    {
    }
}
