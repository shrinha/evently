<?php

namespace Evently\Repository\Eloquent;

use Evently\DomainObjects\ImageDomainObject;
use Evently\Models\Image;
use Evently\Repository\Interfaces\ImageRepositoryInterface;

class ImageRepository extends BaseRepository implements ImageRepositoryInterface
{
    protected function getModel(): string
    {
        return Image::class;
    }

    public function getDomainObject(): string
    {
        return ImageDomainObject::class;
    }
}
