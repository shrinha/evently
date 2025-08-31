<?php

declare(strict_types=1);

namespace Evently\Http\Actions\Events;

use Evently\DomainObjects\EventDomainObject;
use Evently\DomainObjects\OrganizerDomainObject;
use Evently\DomainObjects\ProductCategoryDomainObject;
use Evently\DomainObjects\TaxAndFeesDomainObject;
use Evently\DomainObjects\ProductDomainObject;
use Evently\DomainObjects\ProductPriceDomainObject;
use Evently\Http\Actions\BaseAction;
use Evently\Repository\Eloquent\Value\Relationship;
use Evently\Repository\Interfaces\EventRepositoryInterface;
use Evently\Resources\Event\EventResource;
use Illuminate\Http\JsonResponse;

class GetEventAction extends BaseAction
{
    private EventRepositoryInterface $eventRepository;

    public function __construct(EventRepositoryInterface $eventRepository)
    {
        $this->eventRepository = $eventRepository;
    }

    public function __invoke(int $eventId): JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        $event = $this->eventRepository
            ->loadRelation(new Relationship(domainObject: OrganizerDomainObject::class, name: 'organizer'))
            ->loadRelation(
                new Relationship(ProductCategoryDomainObject::class, [
                    new Relationship(ProductDomainObject::class, [
                        new Relationship(ProductPriceDomainObject::class),
                        new Relationship(TaxAndFeesDomainObject::class),
                    ]),
                ])
            )
            ->findById($eventId);

        return $this->resourceResponse(EventResource::class, $event);
    }
}
