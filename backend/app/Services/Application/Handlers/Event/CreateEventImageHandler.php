<?php

namespace Evently\Services\Application\Handlers\Event;

use Evently\DomainObjects\ImageDomainObject;
use Evently\Services\Application\Handlers\Event\DTO\CreateEventImageDTO;
use Evently\Services\Domain\Event\CreateEventImageService;
use Throwable;

class CreateEventImageHandler
{
    public function __construct(
        private readonly CreateEventImageService $createEventImageService,
    )
    {
    }

    /**
     * @throws Throwable
     */
    public function handle(CreateEventImageDTO $imageData): ImageDomainObject
    {
        return $this->createEventImageService->createImage(
            eventId: $imageData->eventId,
            accountId: $imageData->accountId,
            image: $imageData->image,
            imageType: $imageData->imageType,
        );
    }
}
