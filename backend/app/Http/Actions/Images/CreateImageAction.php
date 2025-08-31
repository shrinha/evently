<?php

namespace Evently\Http\Actions\Images;

use Evently\DomainObjects\Enums\ImageType;
use Evently\Http\Actions\BaseAction;
use Evently\Http\Request\Image\CreateImageRequest;
use Evently\Resources\Image\ImageResource;
use Evently\Services\Application\Handlers\Images\CreateImageHandler;
use Evently\Services\Application\Handlers\Images\DTO\CreateImageDTO;
use Evently\Services\Infrastructure\Image\Exception\CouldNotUploadImageException;
use Illuminate\Http\JsonResponse;

class CreateImageAction extends BaseAction
{
    public function __construct(
        public readonly CreateImageHandler $createImageHandler,
    )
    {
    }

    /**
     * @throws CouldNotUploadImageException
     */
    public function __invoke(CreateImageRequest $request): JsonResponse
    {
        $image = $this->createImageHandler->handle(new CreateImageDTO(
            userId: $this->getAuthenticatedUser()->getId(),
            accountId: $this->getAuthenticatedAccountId(),
            image: $request->file('image'),
            imageType: $request->has('image_type') ? ImageType::fromName($request->input('image_type')) : null,
            entityId: $request->input('entity_id'),
        ));

        return $this->resourceResponse(ImageResource::class, $image);
    }
}
