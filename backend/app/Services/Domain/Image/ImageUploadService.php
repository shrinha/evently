<?php

namespace Evently\Services\Domain\Image;

use Evently\DomainObjects\ImageDomainObject;
use Evently\Repository\Interfaces\ImageRepositoryInterface;
use Evently\Services\Infrastructure\Image\Exception\CouldNotUploadImageException;
use Evently\Services\Infrastructure\Image\ImageStorageService;
use Illuminate\Http\UploadedFile;

class ImageUploadService
{
    public function __construct(
        private readonly ImageStorageService      $imageStorageService,
        private readonly ImageRepositoryInterface $imageRepository
    )
    {
    }

    /**
     * @throws CouldNotUploadImageException
     */
    public function upload(
        UploadedFile $image,
        int          $entityId,
        string       $entityType,
        string       $imageType,
        int          $accountId,
    ): ImageDomainObject
    {
        $storedImage = $this->imageStorageService->store($image, $imageType);

        return $this->imageRepository->create([
            'account_id' => $accountId,
            'entity_id' => $entityId,
            'entity_type' => $entityType,
            'type' => $imageType,
            'filename' => $storedImage->filename,
            'disk' => $storedImage->disk,
            'path' => $storedImage->path,
            'size' => $storedImage->size,
            'mime_type' => $storedImage->mime_type,
        ]);
    }
}
