<?php

namespace Evently\Services\Application\Handlers\Event;

use Evently\DomainObjects\EventSettingDomainObject;
use Evently\DomainObjects\ImageDomainObject;
use Evently\DomainObjects\ProductCategoryDomainObject;
use Evently\DomainObjects\ProductDomainObject;
use Evently\DomainObjects\ProductPriceDomainObject;
use Evently\DomainObjects\Status\EventStatus;
use Evently\DomainObjects\TaxAndFeesDomainObject;
use Evently\Repository\Eloquent\Value\OrderAndDirection;
use Evently\Repository\Eloquent\Value\Relationship;
use Evently\Repository\Interfaces\EventRepositoryInterface;
use Evently\Services\Application\Handlers\Event\DTO\GetPublicOrganizerEventsDTO;
use Illuminate\Pagination\LengthAwarePaginator;

class GetPublicEventsHandler
{
    public function __construct(
        private readonly EventRepositoryInterface $eventRepository,
    )
    {
    }

    public function handle(GetPublicOrganizerEventsDTO $dto): LengthAwarePaginator
    {
        $query = $this->eventRepository
            ->loadRelation(
                new Relationship(ProductCategoryDomainObject::class, [
                    new Relationship(ProductDomainObject::class,
                        nested: [
                            new Relationship(ProductPriceDomainObject::class),
                            new Relationship(TaxAndFeesDomainObject::class),
                        ],
                        orderAndDirections: [
                            new OrderAndDirection('order', 'asc'),
                        ]
                    ),
                ])
            )
            ->loadRelation(new Relationship(EventSettingDomainObject::class))
            ->loadRelation(new Relationship(ImageDomainObject::class));

        if ($dto->authenticatedAccountId) {
            return $query->findEventsForOrganizer(
                organizerId: $dto->organizerId,
                accountId: $dto->authenticatedAccountId,
                params: $dto->queryParams
            );
        }

        return $query->findEvents(
            where: [
                'organizer_id' => $dto->organizerId,
                'status' => EventStatus::LIVE->name,
            ],
            params: $dto->queryParams
        );
    }
}
