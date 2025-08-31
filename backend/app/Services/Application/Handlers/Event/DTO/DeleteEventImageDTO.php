<?php

namespace Evently\Services\Application\Handlers\Event\DTO;

use Evently\DataTransferObjects\BaseDTO;

class DeleteEventImageDTO extends BaseDTO
{
    public function __construct(
        public int $eventId,
        public int $imageId,
    )
    {
    }
}
