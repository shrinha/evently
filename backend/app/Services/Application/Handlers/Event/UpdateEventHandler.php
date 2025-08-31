<?php

namespace Evently\Services\Application\Handlers\Event;

use Evently\DomainObjects\EventDomainObject;
use Evently\DomainObjects\Status\OrderStatus;
use Evently\Events\Dispatcher;
use Evently\Events\EventUpdateEvent;
use Evently\Exceptions\CannotChangeCurrencyException;
use Evently\Helper\DateHelper;
use Evently\Repository\Interfaces\EventRepositoryInterface;
use Evently\Repository\Interfaces\OrderRepositoryInterface;
use Evently\Services\Application\Handlers\Event\DTO\UpdateEventDTO;
use Evently\Services\Infrastructure\HtmlPurifier\HtmlPurifierService;
use Illuminate\Database\DatabaseManager;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Throwable;

readonly class UpdateEventHandler
{
    public function __construct(
        private EventRepositoryInterface $eventRepository,
        private Dispatcher               $dispatcher,
        private DatabaseManager          $databaseManager,
        private OrderRepositoryInterface $orderRepository,
        private HtmlPurifierService      $purifier,
    )
    {
    }

    /**
     * @throws Throwable
     */
    public function handle(UpdateEventDTO $eventData): EventDomainObject
    {
        return $this->databaseManager->transaction(function () use ($eventData) {
            $this->updateEventAttributes($eventData);

            return $this->getUpdateEvent($eventData);
        });
    }

    private function fetchExistingEvent(UpdateEventDTO $eventData)
    {
        $existingEvent = $this->eventRepository->findFirstWhere([
            'id' => $eventData->id,
            'account_id' => $eventData->account_id,
        ]);

        if ($existingEvent === null) {
            throw new ResourceNotFoundException(
                __('Event :id not found', ['id' => $eventData->id])
            );
        }

        return $existingEvent;
    }

    /**
     * @throws CannotChangeCurrencyException
     */
    private function updateEventAttributes(UpdateEventDTO $eventData): void
    {
        $existingEvent = $this->fetchExistingEvent($eventData);

        if ($eventData->currency !== null && $eventData->currency !== $existingEvent->getCurrency()) {
            $this->checkForCompletedOrders($eventData);
        }

        $this->eventRepository->updateWhere(
            attributes: [
                'title' => $eventData->title,
                'category' => $eventData->category?->value ?? $existingEvent->getCategory(),
                'start_date' => DateHelper::convertToUTC($eventData->start_date, $eventData->timezone),
                'end_date' => $eventData->end_date
                    ? DateHelper::convertToUTC($eventData->end_date, $eventData->timezone)
                    : null,
                'description' => $this->purifier->purify($eventData->description),
                'timezone' => $eventData->timezone ?? $existingEvent->getTimezone(),
                'currency' => $eventData->currency ?? $existingEvent->getCurrency(),
                'location' => $eventData->location,
                'location_details' => $eventData->location_details?->toArray(),
            ],
            where: [
                'id' => $eventData->id,
                'account_id' => $eventData->account_id,
            ],
        );
    }

    private function getUpdateEvent(UpdateEventDTO $eventData): EventDomainObject
    {
        $event = $this->eventRepository->findFirstWhere([
            'id' => $eventData->id,
            'account_id' => $eventData->account_id,
        ]);

        $this->dispatcher->dispatchEvent(new EventUpdateEvent($event));

        return $event;
    }

    /**
     * @throws CannotChangeCurrencyException
     */
    private function checkForCompletedOrders(UpdateEventDTO $eventData): void
    {
        $orders = $this->orderRepository->findWhere([
            'event_id' => $eventData->id,
            'status' => OrderStatus::COMPLETED->name,
        ]);

        if (!$orders->isNotEmpty()) {
            throw new CannotChangeCurrencyException(
                __('You cannot change the currency of an event that has completed orders'),
            );
        }
    }
}

