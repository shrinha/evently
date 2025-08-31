<?php

namespace Evently\Services\Domain\Event;

use Evently\DomainObjects\Enums\ImageType;
use Evently\DomainObjects\EventDomainObject;
use Evently\DomainObjects\ImageDomainObject;
use Evently\Repository\Interfaces\ImageRepositoryInterface;
use Evently\Services\Domain\Image\ImageUploadService;
use Illuminate\Database\DatabaseManager;
use Illuminate\Http\UploadedFile;
use Throwable;

class CreateEventImageService
{
    public function __construct(
        private readonly ImageUploadService       $imageUploadService,
        private readonly ImageRepositoryInterface $imageRepository,
        private readonly DatabaseManager          $databaseManager,
    )
    {
    }

    /**
     * @throws Throwable
     */
    public function createImage(
        int          $eventId,
        int          $accountId,
        UploadedFile $image,
        ImageType    $imageType,
    ): ImageDomainObject
    {
        return $this->databaseManager->transaction(function () use ($accountId, $image, $eventId, $imageType) {
            if ($imageType === ImageType::EVENT_COVER) {
                $this->imageRepository->deleteWhere([
                    'entity_id' => $eventId,
                    'entity_type' => EventDomainObject::class,
                    'type' => ImageType::EVENT_COVER->name,
                ]);
            }

            return $this->imageUploadService->upload(
                image: $image,
                entityId: $eventId,
                entityType: EventDomainObject::class,
                imageType: $imageType->name,
                accountId: $accountId,
            );
        });
    }
}
