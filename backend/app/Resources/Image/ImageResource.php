<?php

namespace Evently\Resources\Image;

use Evently\DomainObjects\ImageDomainObject;
use Evently\Helper\Url;
use Evently\Resources\BaseResource;

/**
 * @mixin ImageDomainObject
 */
class ImageResource extends BaseResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->getId(),
            'url' => Url::getCdnUrl($this->getPath()),
            'path' => $this->getPath(),
            'size' => $this->getSize(),
            'file_name' => $this->getFileName(),
            'mime_type' => $this->getMimeType(),
            'type' => $this->getType()
        ];
    }
}
