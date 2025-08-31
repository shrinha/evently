<?php

namespace Evently\Services\Domain\Event;

use Evently\DomainObjects\AffiliateDomainObject;
use Evently\DomainObjects\CapacityAssignmentDomainObject;
use Evently\DomainObjects\CheckInListDomainObject;
use Evently\DomainObjects\Enums\ImageType;
use Evently\DomainObjects\Enums\QuestionBelongsTo;
use Evently\DomainObjects\EventDomainObject;
use Evently\DomainObjects\EventSettingDomainObject;
use Evently\DomainObjects\ImageDomainObject;
use Evently\DomainObjects\ProductCategoryDomainObject;
use Evently\DomainObjects\ProductDomainObject;
use Evently\DomainObjects\ProductPriceDomainObject;
use Evently\DomainObjects\PromoCodeDomainObject;
use Evently\DomainObjects\QuestionDomainObject;
use Evently\DomainObjects\Status\EventStatus;
use Evently\DomainObjects\TaxAndFeesDomainObject;
use Evently\DomainObjects\WebhookDomainObject;
use Evently\Repository\Eloquent\Value\Relationship;
use Evently\Repository\Interfaces\AffiliateRepositoryInterface;
use Evently\Repository\Interfaces\EventRepositoryInterface;
use Evently\Repository\Interfaces\ImageRepositoryInterface;
use Evently\Services\Domain\CapacityAssignment\CreateCapacityAssignmentService;
use Evently\Services\Domain\CheckInList\CreateCheckInListService;
use Evently\Services\Domain\CreateWebhookService;
use Evently\Services\Domain\Product\CreateProductService;
use Evently\Services\Domain\ProductCategory\CreateProductCategoryService;
use Evently\Services\Domain\PromoCode\CreatePromoCodeService;
use Evently\Services\Domain\Question\CreateQuestionService;
use Evently\Services\Infrastructure\HtmlPurifier\HtmlPurifierService;
use Illuminate\Database\DatabaseManager;
use Throwable;

class DuplicateEventService
{
    public function __construct(
        private readonly EventRepositoryInterface        $eventRepository,
        private readonly CreateEventService              $createEventService,
        private readonly CreateProductService            $createProductService,
        private readonly CreateQuestionService           $createQuestionService,
        private readonly CreatePromoCodeService          $createPromoCodeService,
        private readonly CreateCapacityAssignmentService $createCapacityAssignmentService,
        private readonly CreateCheckInListService        $createCheckInListService,
        private readonly ImageRepositoryInterface        $imageRepository,
        private readonly DatabaseManager                 $databaseManager,
        private readonly HtmlPurifierService             $purifier,
        private readonly CreateProductCategoryService    $createProductCategoryService,
        private readonly CreateWebhookService            $createWebhookService,
        private readonly AffiliateRepositoryInterface    $affiliateRepository,
    )
    {
    }

    /**
     * @throws Throwable
     */
    public function duplicateEvent(
        string  $eventId,
        string  $accountId,
        string  $title,
        string  $startDate,
        bool    $duplicateProducts = true,
        bool    $duplicateQuestions = true,
        bool    $duplicateSettings = true,
        bool    $duplicatePromoCodes = true,
        bool    $duplicateCapacityAssignments = true,
        bool    $duplicateCheckInLists = true,
        bool    $duplicateEventCoverImage = true,
        bool    $duplicateWebhooks = true,
        bool    $duplicateAffiliates = true,
        ?string $description = null,
        ?string $endDate = null,
    ): EventDomainObject
    {
        try {
            $this->databaseManager->beginTransaction();

            $event = $this->getEventWithRelations($eventId, $accountId);

            $event
                ->setTitle($title)
                ->setStartDate($startDate)
                ->setEndDate($endDate)
                ->setDescription($this->purifier->purify($description))
                ->setStatus(EventStatus::DRAFT->name);

            $newEvent = $this->cloneExistingEvent(
                event: $event,
                cloneEventSettings: $duplicateSettings,
            );

            if ($duplicateQuestions) {
                $this->clonePerOrderQuestions($event, $newEvent->getId());
            }

            if ($duplicateProducts) {
                $this->cloneExistingProducts(
                    event: $event,
                    newEventId: $newEvent->getId(),
                    duplicateQuestions: $duplicateQuestions,
                    duplicatePromoCodes: $duplicatePromoCodes,
                    duplicateCapacityAssignments: $duplicateCapacityAssignments,
                    duplicateCheckInLists: $duplicateCheckInLists,
                );
            } else {
                $this->createProductCategoryService->createDefaultProductCategory($newEvent);
            }

            if ($duplicateEventCoverImage) {
                $this->cloneEventCoverImage($event, $newEvent->getId());
            }

            if ($duplicateWebhooks) {
                $this->duplicateWebhooks($event, $newEvent);
            }

            if ($duplicateAffiliates) {
                $this->duplicateAffiliates($event, $newEvent);
            }

            $this->databaseManager->commit();

            return $this->getEventWithRelations($newEvent->getId(), $newEvent->getAccountId());
        } catch (Throwable $e) {
            $this->databaseManager->rollBack();
            throw $e;
        }
    }

    /**
     * @param EventDomainObject $event
     * @param bool $cloneEventSettings
     * @return EventDomainObject
     * @throws Throwable
     */
    private function cloneExistingEvent(EventDomainObject $event, bool $cloneEventSettings): EventDomainObject
    {
        return $this->createEventService->createEvent(
            eventData: (new EventDomainObject())
                ->setOrganizerId($event->getOrganizerId())
                ->setAccountId($event->getAccountId())
                ->setUserId($event->getUserId())
                ->setTitle($event->getTitle())
                ->setCategory($event->getCategory())
                ->setStartDate($event->getStartDate())
                ->setEndDate($event->getEndDate())
                ->setDescription($event->getDescription())
                ->setAttributes($event->getAttributes())
                ->setTimezone($event->getTimezone())
                ->setCurrency($event->getCurrency())
                ->setStatus($event->getStatus()),
            eventSettings: $cloneEventSettings ? $event->getEventSettings() : null,
        );
    }

    /**
     * @throws Throwable
     */
    private function cloneExistingProducts(
        EventDomainObject $event,
        int               $newEventId,
        bool              $duplicateQuestions,
        bool              $duplicatePromoCodes,
        bool              $duplicateCapacityAssignments,
        bool              $duplicateCheckInLists,
    ): void
    {
        $oldProductToNewProductMap = [];

        $event->getProductCategories()?->each(function (ProductCategoryDomainObject $productCategory) use ($event, $newEventId, &$oldProductToNewProductMap) {
            $newCategory = $this->createProductCategoryService->createCategory(
                (new ProductCategoryDomainObject())
                    ->setName($productCategory->getName())
                    ->setNoProductsMessage($productCategory->getNoProductsMessage())
                    ->setDescription($productCategory->getDescription())
                    ->setIsHidden($productCategory->getIsHidden())
                    ->setEventId($newEventId),
            );

            /** @var ProductDomainObject $product */
            foreach ($productCategory->getProducts() as $product) {
                $product->setEventId($newEventId);
                $product->setProductCategoryId($newCategory->getId());
                $newProduct = $this->createProductService->createProduct(
                    product: $product,
                    accountId: $event->getAccountId(),
                    taxAndFeeIds: $product->getTaxAndFees()?->map(fn($taxAndFee) => $taxAndFee->getId())?->toArray(),
                );
                $oldProductToNewProductMap[$product->getId()] = $newProduct->getId();
            }
        });

        if ($duplicateQuestions) {
            $this->clonePerProductQuestions($event, $newEventId, $oldProductToNewProductMap);
        }

        if ($duplicatePromoCodes) {
            $this->clonePromoCodes($event, $newEventId, $oldProductToNewProductMap);
        }

        if ($duplicateCapacityAssignments) {
            $this->cloneCapacityAssignments($event, $newEventId, $oldProductToNewProductMap);
        }

        if ($duplicateCheckInLists) {
            $this->cloneCheckInLists($event, $newEventId, $oldProductToNewProductMap);
        }
    }

    /**
     * @throws Throwable
     */
    private function clonePerProductQuestions(EventDomainObject $event, int $newEventId, array $oldProductToNewProductMap): void
    {
        foreach ($event->getQuestions() as $question) {
            if ($question->getBelongsTo() === QuestionBelongsTo::PRODUCT->name) {
                $this->createQuestionService->createQuestion(
                    (new QuestionDomainObject())
                        ->setTitle($question->getTitle())
                        ->setEventId($newEventId)
                        ->setBelongsTo($question->getBelongsTo())
                        ->setType($question->getType())
                        ->setRequired($question->getRequired())
                        ->setOptions($question->getOptions())
                        ->setIsHidden($question->getIsHidden()),
                    array_map(
                        static fn(ProductDomainObject $product) => $oldProductToNewProductMap[$product->getId()],
                        $question->getProducts()?->all(),
                    ),
                );
            }
        }
    }

    /**
     * @throws Throwable
     */
    private function clonePerOrderQuestions(EventDomainObject $event, int $newEventId): void
    {
        foreach ($event->getQuestions() as $question) {
            if ($question->getBelongsTo() === QuestionBelongsTo::ORDER->name) {
                $this->createQuestionService->createQuestion(
                    (new QuestionDomainObject())
                        ->setTitle($question->getTitle())
                        ->setEventId($newEventId)
                        ->setBelongsTo($question->getBelongsTo())
                        ->setType($question->getType())
                        ->setRequired($question->getRequired())
                        ->setOptions($question->getOptions())
                        ->setIsHidden($question->getIsHidden()),
                    [],
                );
            }
        }
    }

    /**
     * @throws Throwable
     */
    private function clonePromoCodes(EventDomainObject $event, int $newEventId, array $oldProductToNewProductMap): void
    {
        foreach ($event->getPromoCodes() as $promoCode) {
            $this->createPromoCodeService->createPromoCode(
                (new PromoCodeDomainObject())
                    ->setCode($promoCode->getCode())
                    ->setEventId($newEventId)
                    ->setApplicableProductIds(array_map(
                        static fn($productId) => $oldProductToNewProductMap[$productId],
                        $promoCode->getApplicableProductIds() ?? [],
                    ))
                    ->setDiscountType($promoCode->getDiscountType())
                    ->setDiscount($promoCode->getDiscount())
                    ->setExpiryDate($promoCode->getExpiryDate())
                    ->setMaxAllowedUsages($promoCode->getMaxAllowedUsages()),
            );
        }
    }

    private function cloneCapacityAssignments(EventDomainObject $event, int $newEventId, $oldProductToNewProductMap): void
    {
        /** @var CapacityAssignmentDomainObject $capacityAssignment */
        foreach ($event->getCapacityAssignments() as $capacityAssignment) {
            $this->createCapacityAssignmentService->createCapacityAssignment(
                capacityAssignment: (new CapacityAssignmentDomainObject())
                    ->setName($capacityAssignment->getName())
                    ->setEventId($newEventId)
                    ->setCapacity($capacityAssignment->getCapacity())
                    ->setAppliesTo($capacityAssignment->getAppliesTo())
                    ->setStatus($capacityAssignment->getStatus()),
                productIds: $capacityAssignment->getProducts()
                ?->map(fn($product) => $oldProductToNewProductMap[$product->getId()])?->toArray() ?? [],
            );
        }
    }

    private function cloneCheckInLists(EventDomainObject $event, int $newEventId, $oldProductToNewProductMap): void
    {
        foreach ($event->getCheckInLists() as $checkInList) {
            $this->createCheckInListService->createCheckInList(
                checkInList: (new CheckInListDomainObject())
                    ->setName($checkInList->getName())
                    ->setDescription($checkInList->getDescription())
                    ->setExpiresAt($checkInList->getExpiresAt())
                    ->setActivatesAt($checkInList->getActivatesAt())
                    ->setEventId($newEventId),
                productIds: $checkInList->getProducts()
                ?->map(fn($product) => $oldProductToNewProductMap[$product->getId()])?->toArray() ?? [],
            );
        }
    }

    private function cloneEventCoverImage(EventDomainObject $event, int $newEventId): void
    {
        /** @var ImageDomainObject $coverImage */
        $coverImage = $event->getImages()?->first(fn(ImageDomainObject $image) => $image->getType() === ImageType::EVENT_COVER->name);
        if ($coverImage) {
            $this->imageRepository->create([
                'entity_id' => $newEventId,
                'entity_type' => EventDomainObject::class,
                'type' => ImageType::EVENT_COVER->name,
                'disk' => $coverImage->getDisk(),
                'path' => $coverImage->getPath(),
                'filename' => $coverImage->getFileName(),
                'size' => $coverImage->getSize(),
                'mime_type' => $coverImage->getMimeType(),
            ]);
        }
    }

    private function getEventWithRelations(string $eventId, string $accountId): EventDomainObject
    {
        return $this->eventRepository
            ->loadRelation(EventSettingDomainObject::class)
            ->loadRelation(
                new Relationship(ProductCategoryDomainObject::class, [
                    new Relationship(ProductDomainObject::class, [
                        new Relationship(ProductPriceDomainObject::class),
                        new Relationship(TaxAndFeesDomainObject::class),
                    ]),
                ])
            )
            ->loadRelation(PromoCodeDomainObject::class)
            ->loadRelation(new Relationship(QuestionDomainObject::class, [
                new Relationship(ProductDomainObject::class),
            ]))
            ->loadRelation(new Relationship(CapacityAssignmentDomainObject::class, [
                new Relationship(ProductDomainObject::class),
            ]))
            ->loadRelation(new Relationship(CheckInListDomainObject::class, [
                new Relationship(ProductDomainObject::class),
            ]))
            ->loadRelation(ImageDomainObject::class)
            ->loadRelation(WebhookDomainObject::class)
            ->loadRelation(AffiliateDomainObject::class)
            ->findFirstWhere([
                'id' => $eventId,
                'account_id' => $accountId,
            ]);
    }

    private function duplicateWebhooks(EventDomainObject $event, EventDomainObject $newEvent): void
    {
        $event->getWebhooks()?->each(function (WebhookDomainObject $webhook) use ($newEvent) {
            $this->createWebhookService->createWebhook(
                (new WebhookDomainObject())
                    ->setEventId($newEvent->getId())
                    ->setUrl($webhook->getUrl())
                    ->setSecret($webhook->getSecret())
                    ->setEventTypes($webhook->getEventTypes())
                    ->setStatus($webhook->getStatus())
                    ->setAccountId($newEvent->getAccountId())
                    ->setUserId($newEvent->getUserId()),
            );
        });
    }

    private function duplicateAffiliates(EventDomainObject $event, EventDomainObject $newEvent): void
    {
        $event->getAffiliates()?->each(function (AffiliateDomainObject $affiliate) use ($newEvent) {
            $this->affiliateRepository->create([
                'event_id' => $newEvent->getId(),
                'account_id' => $newEvent->getAccountId(),
                'name' => $affiliate->getName(),
                'code' => $affiliate->getCode(),
                'email' => $affiliate->getEmail(),
                'status' => $affiliate->getStatus(),
            ]);
        });
    }
}
