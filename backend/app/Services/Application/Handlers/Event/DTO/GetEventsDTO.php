<?php

namespace Evently\Services\Application\Handlers\Event\DTO;

use Evently\DataTransferObjects\BaseDTO;
use Evently\Http\DTO\QueryParamsDTO;

class GetEventsDTO extends BaseDTO
{
    public function __construct(
        public int $accountId,
        public QueryParamsDTO $queryParams,
    )
    {
    }
}
