<?php

namespace Evently\Services\Infrastructure\Webhook;

use Evently\DomainObjects\AttendeeDomainObject;
use Evently\DomainObjects\OrderItemDomainObject;
use Evently\DomainObjects\ProductPriceDomainObject;
use Evently\DomainObjects\QuestionAndAnswerViewDomainObject;
use Evently\DomainObjects\Status\WebhookStatus;
use Evently\DomainObjects\TaxAndFeesDomainObject;
use Evently\DomainObjects\WebhookDomainObject;
use Evently\Repository\Eloquent\Value\Relationship;
use Evently\Repository\Interfaces\AttendeeCheckInRepositoryInterface;
use Evently\Repository\Interfaces\AttendeeRepositoryInterface;
use Evently\Repository\Interfaces\OrderRepositoryInterface;
use Evently\Repository\Interfaces\ProductRepositoryInterface;
use Evently\Repository\Interfaces\WebhookRepositoryInterface;
use Evently\Resources\Attendee\AttendeeResource;
use Evently\Resources\CheckInList\AttendeeCheckInResource;
use Evently\Resources\Order\OrderResource;
use Evently\Resources\Product\ProductResource;
use Evently\Services\Infrastructure\DomainEvents\Enums\DomainEventType;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use Psr\Log\LoggerInterface;
use Spatie\WebhookServer\WebhookCall;

class WebhookDispatchService
{
    public function __construct(
        private readonly LoggerInterface                    $logger,
        private readonly WebhookRepositoryInterface         $webhookRepository,
        private readonly OrderRepositoryInterface           $orderRepository,
        private readonly ProductRepositoryInterface         $productRepository,
        private readonly AttendeeRepositoryInterface        $attendeeRepository,
        private readonly AttendeeCheckInRepositoryInterface $attendeeCheckInRepository,
    )
    {
    }

    public function dispatchAttendeeWebhook(DomainEventType $eventType, int $attendeeId): void
    {
        $attendee = $this->attendeeRepository
            ->loadRelation(new Relationship(
                domainObject: QuestionAndAnswerViewDomainObject::class,
                name: 'question_and_answer_views',
            ))
            ->findById($attendeeId);

        $this->dispatchWebhook(
            eventType: $eventType,
            payload: new AttendeeResource($attendee),
            eventId: $attendee->getEventId(),
        );
    }

    public function dispatchCheckInWebhook(DomainEventType $eventType, int $attendeeCheckInId): void
    {
        $attendeeCheckIn = $this->attendeeCheckInRepository
            ->loadRelation(new Relationship(
                domainObject: AttendeeDomainObject::class,
                name: 'attendee',
            ))
            ->includeDeleted()
            ->findById($attendeeCheckInId);

        $this->dispatchWebhook(
            eventType: $eventType,
            payload: new AttendeeCheckInResource($attendeeCheckIn),
            eventId: $attendeeCheckIn->getEventId(),
        );
    }

    public function dispatchProductWebhook(DomainEventType $eventType, int $productId): void
    {
        $product = $this->productRepository
            ->loadRelation(ProductPriceDomainObject::class)
            ->loadRelation(TaxAndFeesDomainObject::class)
            ->includeDeleted()
            ->findById($productId);

        $this->dispatchWebhook(
            eventType: $eventType,
            payload: new ProductResource($product),
            eventId: $product->getEventId(),
        );
    }

    public function dispatchOrderWebhook(DomainEventType $eventType, int $orderId): void
    {
        $order = $this->orderRepository
            ->loadRelation(OrderItemDomainObject::class)
            ->loadRelation(new Relationship(
                    domainObject: AttendeeDomainObject::class,
                    nested: [
                        new Relationship(
                            domainObject: QuestionAndAnswerViewDomainObject::class,
                            name: 'question_and_answer_views',
                        ),
                    ],
                    name: 'attendees')
            )
            ->loadRelation(QuestionAndAnswerViewDomainObject::class)
            ->findById($orderId);

        if ($eventType === DomainEventType::ORDER_CREATED) {
            /** @var AttendeeDomainObject $attendee */
            foreach ($order->getAttendees() as $attendee) {
                $this->dispatchAttendeeWebhook(
                    eventType: DomainEventType::ATTENDEE_CREATED,
                    attendeeId: $attendee->getId(),
                );
            }
        }

        if ($eventType === DomainEventType::ORDER_CANCELLED) {
            /** @var AttendeeDomainObject $attendee */
            foreach ($order->getAttendees() as $attendee) {
                $this->dispatchAttendeeWebhook(
                    eventType: DomainEventType::ATTENDEE_CANCELLED,
                    attendeeId: $attendee->getId(),
                );
            }
        }

        $this->dispatchWebhook(
            $eventType,
            new OrderResource($order),
            $order->getEventId(),
        );
    }

    private function dispatchWebhook(DomainEventType $eventType, JsonResource $payload, int $eventId): void
    {
        /** @var Collection<WebhookDomainObject> $webhooks */
        $webhooks = $this->webhookRepository->findWhere([
            'event_id' => $eventId,
            'status' => WebhookStatus::ENABLED->name,
        ])
            ->filter(fn(WebhookDomainObject $webhook) => in_array($eventType->value, $webhook->getEventTypes(), true));

        foreach ($webhooks as $webhook) {
            $this->logger->info("Dispatching webhook for event ID: $eventId and webhook ID: {$webhook->getId()}");

            WebhookCall::create()
                ->url($webhook->getUrl())
                ->payload([
                    'event_type' => $eventType->value,
                    'event_sent_at' => now()->toIso8601String(),
                    'payload' => $payload->resolve()
                ])
                ->useSecret($webhook->getSecret())
                ->meta([
                    'webhook_id' => $webhook->getId(),
                    'event_id' => $eventId,
                    'event_type' => $eventType->name,
                ])
                ->dispatchSync();
        }
    }
}
