<?php

namespace Evently\Services\Application\Handlers\Event;

use Evently\DomainObjects\EventDomainObject;
use Evently\DomainObjects\EventSettingDomainObject;
use Evently\DomainObjects\Generated\PromoCodeDomainObjectAbstract;
use Evently\DomainObjects\ImageDomainObject;
use Evently\DomainObjects\OrganizerDomainObject;
use Evently\DomainObjects\OrganizerSettingDomainObject;
use Evently\DomainObjects\ProductCategoryDomainObject;
use Evently\DomainObjects\ProductDomainObject;
use Evently\DomainObjects\ProductPriceDomainObject;
use Evently\DomainObjects\TaxAndFeesDomainObject;
use Evently\Repository\Eloquent\Value\OrderAndDirection;
use Evently\Repository\Eloquent\Value\Relationship;
use Evently\Repository\Interfaces\EventRepositoryInterface;
use Evently\Repository\Interfaces\PromoCodeRepositoryInterface;
use Evently\Services\Application\Handlers\Event\DTO\GetPublicEventDTO;
use Evently\Services\Domain\Event\EventPageViewIncrementService;
use Evently\Services\Domain\Product\ProductFilterService;

class GetPublicEventHandler
{
    public function __construct(
        private readonly EventRepositoryInterface      $eventRepository,
        private readonly PromoCodeRepositoryInterface  $promoCodeRepository,
        private readonly ProductFilterService          $productFilterService,
        private readonly EventPageViewIncrementService $eventPageViewIncrementService,
    )
    {
    }

    public function handle(GetPublicEventDTO $data): EventDomainObject
    {
        $event = $this->eventRepository
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
            ->loadRelation(new Relationship(ImageDomainObject::class))
            ->loadRelation(new Relationship(OrganizerDomainObject::class, nested: [
                new Relationship(ImageDomainObject::class),
                new Relationship(OrganizerSettingDomainObject::class),
            ], name: 'organizer'))
            ->findById($data->eventId);

        $promoCodeDomainObject = $this->promoCodeRepository->findFirstWhere([
            PromoCodeDomainObjectAbstract::EVENT_ID => $data->eventId,
            PromoCodeDomainObjectAbstract::CODE => $data->promoCode,
        ]);

        if (!$promoCodeDomainObject?->isValid()) {
            $promoCodeDomainObject = null;
        }

        if (!$data->isAuthenticated) {
            $this->eventPageViewIncrementService->increment($data->eventId, $data->ipAddress);
        }

        return $event->setProductCategories($this->productFilterService->filter(
            productsCategories: $event->getProductCategories(),
            promoCode: $promoCodeDomainObject
        ));
    }
}
