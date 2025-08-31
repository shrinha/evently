<?php

namespace Evently\Resources\CapacityAssignment;

use Evently\DomainObjects\CapacityAssignmentDomainObject;
use Evently\DomainObjects\Enums\CapacityAssignmentAppliesTo;
use Evently\DomainObjects\ProductDomainObject;
use Evently\Resources\BaseResource;
use Illuminate\Http\Request;

/**
 * @mixin CapacityAssignmentDomainObject
 */
class CapacityAssignmentResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'capacity' => $this->getCapacity(),
            'used_capacity' => $this->getUsedCapacity(),
            'percentage_used' => $this->getPercentageUsed(),
            'applies_to' => $this->getAppliesTo(),
            'status' => $this->getStatus(),
            'event_id' => $this->getEventId(),
            $this->mergeWhen(
                condition: $this->getProducts() !== null && $this->getAppliesTo() === CapacityAssignmentAppliesTo::PRODUCTS->name,
                value: [
                    'products' => $this->getProducts()?->map(fn(ProductDomainObject $product) => [
                        'id' => $product->getId(),
                        'title' => $product->getTitle(),
                    ]),
                ]),
        ];
    }
}
