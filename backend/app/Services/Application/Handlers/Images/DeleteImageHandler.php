<?php

namespace Evently\Services\Application\Handlers\Images;

use Evently\DomainObjects\ImageDomainObject;
use Evently\Exceptions\CannotDeleteEntityException;
use Evently\Repository\Interfaces\ImageRepositoryInterface;
use Evently\Services\Application\Handlers\Images\DTO\DeleteImageDTO;

class DeleteImageHandler
{
    public function __construct(
        private readonly ImageRepositoryInterface $imageRepository,
    )
    {
    }

    /**
     * @throws CannotDeleteEntityException
     */
    public function handle(DeleteImageDTO $imageData): void
    {
        /** @var ImageDomainObject $image */
        $image = $this->imageRepository->findFirstWhere([
            'id' => $imageData->imageId,
            'account_id' => $imageData->accountId,
        ]);

        if ($image === null) {
            throw new CannotDeleteEntityException('You do not have permission to delete this image.');
        }

        $this->imageRepository->deleteWhere([
            'id' => $imageData->imageId,
            'account_id' => $imageData->accountId,
        ]);
    }
}
