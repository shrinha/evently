<?php

namespace Evently\Http\Actions\Attendees;

use Evently\DomainObjects\AttendeeDomainObject;
use Evently\DomainObjects\EventDomainObject;
use Evently\DomainObjects\OrderDomainObject;
use Evently\Http\Actions\BaseAction;
use Evently\Http\DTO\QueryParamsDTO;
use Evently\Repository\Eloquent\Value\Relationship;
use Evently\Repository\Interfaces\AttendeeRepositoryInterface;
use Evently\Resources\Attendee\AttendeeResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @todo move to handler
 * @todo - add validation for filter fields
 */
class GetAttendeesAction extends BaseAction
{
    private AttendeeRepositoryInterface $attendeeRepository;

    public function __construct(AttendeeRepositoryInterface $attendeeRepository)
    {
        $this->attendeeRepository = $attendeeRepository;
    }

    public function __invoke(int $eventId, Request $request): JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        $attendees = $this->attendeeRepository
            ->loadRelation(new Relationship(
                domainObject: OrderDomainObject::class,
                name: 'order'
            ))
            ->findByEventId($eventId, QueryParamsDTO::fromArray($request->query->all()));

        return $this->filterableResourceResponse(
            resource: AttendeeResource::class,
            data: $attendees,
            domainObject: AttendeeDomainObject::class,
        );
    }
}
