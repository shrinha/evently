<?php

namespace Evently\Services\Application\Handlers\Images\DTO;

use Evently\DomainObjects\Enums\ImageType;
use Illuminate\Http\UploadedFile;

class CreateImageDTO
{
    public function __construct(
        public readonly int          $userId,
        public readonly int          $accountId,
        public readonly UploadedFile $image,
        public readonly ?ImageType   $imageType = null,
        public readonly ?int         $entityId = null,
    )
    {
    }

    public function isGeneric(): bool
    {
        return $this->imageType === null && $this->entityId === null;
    }
}
