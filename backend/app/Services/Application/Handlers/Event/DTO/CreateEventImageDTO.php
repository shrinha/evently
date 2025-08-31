<?php

namespace Evently\Services\Application\Handlers\Event\DTO;

use Evently\DataTransferObjects\BaseDTO;
use Evently\DomainObjects\Enums\ImageType;
use Illuminate\Http\UploadedFile;

class CreateEventImageDTO extends BaseDTO
{
    public function __construct(
        public readonly int          $eventId,
        public readonly int          $accountId,
        public readonly UploadedFile $image,
        public readonly ImageType    $imageType,
    )
    {
    }
}
