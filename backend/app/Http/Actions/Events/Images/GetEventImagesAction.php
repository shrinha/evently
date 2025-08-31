<?php

namespace Evently\Http\Actions\Events\Images;

use Evently\DomainObjects\Enums\ImageType;
use Evently\DomainObjects\EventDomainObject;
use Evently\Http\Actions\BaseAction;
use Evently\Repository\Interfaces\ImageRepositoryInterface;
use Evently\Resources\Image\ImageResource;
use Illuminate\Http\JsonResponse;

class GetEventImagesAction extends BaseAction
{
    public function __construct(private readonly ImageRepositoryInterface $imageRepository)
    {
    }

    public function __invoke(int $eventId): JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        $images = $this->imageRepository->findWhere([
            'entity_id' => $eventId,
            'entity_type' => EventDomainObject::class,
            'type' => ImageType::EVENT_COVER->name,
        ]);

        return $this->resourceResponse(ImageResource::class, $images);
    }
}
