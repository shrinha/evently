<?php

namespace Evently\Http\Actions\Attendees;

use Evently\DomainObjects\AttendeeCheckInDomainObject;
use Evently\DomainObjects\EventDomainObject;
use Evently\DomainObjects\ProductDomainObject;
use Evently\DomainObjects\ProductPriceDomainObject;
use Evently\DomainObjects\QuestionAndAnswerViewDomainObject;
use Evently\Http\Actions\BaseAction;
use Evently\Repository\Eloquent\Value\Relationship;
use Evently\Repository\Interfaces\AttendeeRepositoryInterface;
use Evently\Resources\Attendee\AttendeeResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class GetAttendeeAction extends BaseAction
{
    private AttendeeRepositoryInterface $attendeeRepository;

    public function __construct(AttendeeRepositoryInterface $attendeeRepository)
    {
        $this->attendeeRepository = $attendeeRepository;
    }

    public function __invoke(int $eventId, int $attendeeId): Response|JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        $attendee = $this->attendeeRepository
            ->loadRelation(relationship: QuestionAndAnswerViewDomainObject::class)
            ->loadRelation(new Relationship(
                domainObject: ProductDomainObject::class,
                nested: [
                    new Relationship(
                        domainObject: ProductPriceDomainObject::class,
                    ),
                ], name: 'product'))
            ->loadRelation(new Relationship(
                domainObject: AttendeeCheckInDomainObject::class,
                name: 'check_in',
            ))
            ->findFirstWhere([
                'id' => $attendeeId,
                'event_id' => $eventId,
            ]);

        if (!$attendee) {
            return $this->notFoundResponse();
        }

        return $this->resourceResponse(AttendeeResource::class, $attendee);
    }
}
