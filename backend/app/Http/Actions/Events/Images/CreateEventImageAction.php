<?php

namespace Evently\Http\Actions\Events\Images;

use Evently\DomainObjects\Enums\ImageType;
use Evently\DomainObjects\EventDomainObject;
use Evently\Http\Actions\BaseAction;
use Evently\Http\Request\Event\CreateEventImageRequest;
use Evently\Resources\Image\ImageResource;
use Evently\Services\Application\Handlers\Event\CreateEventImageHandler;
use Evently\Services\Application\Handlers\Event\DTO\CreateEventImageDTO;
use Illuminate\Http\JsonResponse;

class CreateEventImageAction extends BaseAction
{
    private CreateEventImageHandler $createEventImageHandler;

    public function __construct(CreateEventImageHandler $createEventImageHandler)
    {
        $this->createEventImageHandler = $createEventImageHandler;
    }

    public function __invoke(CreateEventImageRequest $request, int $eventId): JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        $payload = array_merge($request->validated(), [
            'event_id' => $eventId,
        ]);

        $image = $this->createEventImageHandler->handle(new CreateEventImageDTO(
            eventId: $payload['event_id'],
            accountId: $this->getAuthenticatedAccountId(),
            image: $request->file('image'),
            imageType: ImageType::fromName($payload['type']),
        ));

        return $this->resourceResponse(ImageResource::class, $image);
    }
}
