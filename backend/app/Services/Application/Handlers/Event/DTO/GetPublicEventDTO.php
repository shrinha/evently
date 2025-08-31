<?php

namespace Evently\Services\Application\Handlers\Event\DTO;

use Evently\DataTransferObjects\BaseDTO;

class GetPublicEventDTO extends BaseDTO
{
    public function __construct(
        public int     $eventId,
        public bool    $isAuthenticated,
        public ?string $ipAddress = null,
        public ?string $promoCode = null,
    )
    {
    }
}
