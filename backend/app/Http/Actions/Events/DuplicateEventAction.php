<?php

namespace Evently\Http\Actions\Events;

use Evently\DomainObjects\EventDomainObject;
use Evently\Http\Actions\BaseAction;
use Evently\Http\Request\Event\DuplicateEventRequest;
use Evently\Resources\Event\EventResource;
use Evently\Services\Application\Handlers\Event\DuplicateEventHandler;
use Evently\Services\Domain\Event\DTO\DuplicateEventDataDTO;
use Illuminate\Http\JsonResponse;
use Throwable;

class DuplicateEventAction extends BaseAction
{
    public function __construct(private readonly DuplicateEventHandler $handler)
    {
    }

    /**
     * @throws Throwable
     */
    public function __invoke(int $eventId, DuplicateEventRequest $request): JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        $event = $this->handler->handle(new DuplicateEventDataDTO(
            eventId: $eventId,
            accountId: $this->getAuthenticatedAccountId(),
            title: $request->validated('title'),
            startDate: $request->validated('start_date'),
            duplicateProducts: $request->validated('duplicate_products'),
            duplicateQuestions: $request->validated('duplicate_questions'),
            duplicateSettings: $request->validated('duplicate_settings'),
            duplicatePromoCodes: $request->validated('duplicate_promo_codes'),
            duplicateCapacityAssignments: $request->validated('duplicate_capacity_assignments'),
            duplicateCheckInLists: $request->validated('duplicate_check_in_lists'),
            duplicateEventCoverImage: $request->validated('duplicate_event_cover_image'),
            duplicateWebhooks: $request->validated('duplicate_webhooks'),
            duplicateAffiliates: $request->validated('duplicate_affiliates'),
            description: $request->validated('description'),
            endDate: $request->validated('end_date'),
        ));

        return $this->resourceResponse(EventResource::class, $event);
    }
}
