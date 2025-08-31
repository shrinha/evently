<?php

namespace Evently\Resources\Organizer;

use Evently\DomainObjects\OrganizerDomainObject;
use Evently\Resources\Image\ImageResource;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin OrganizerDomainObject
 */
class OrganizerResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'email' => $this->getEmail(),
            'phone' => $this->getPhone(),
            'website' => $this->getWebsite(),
            'description' => $this->getDescription(),
            'timezone' => $this->getTimezone(),
            'currency' => $this->getCurrency(),
            'slug' => $this->getSlug(),
            'status' => $this->getStatus(),
            'images' => $this->when(
                (bool)$this->getImages(),
                fn() => ImageResource::collection($this->getImages())
            ),
            'settings' => $this->when(
                condition: !is_null($this->getOrganizerSettings()),
                value: fn() => new OrganizerSettingsResource($this->getOrganizerSettings())
            ),
        ];
    }
}
