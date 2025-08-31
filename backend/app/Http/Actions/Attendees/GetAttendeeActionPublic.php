<?php

namespace Evently\Http\Actions\Attendees;

use Evently\DomainObjects\Generated\AttendeeDomainObjectAbstract;
use Evently\DomainObjects\ProductDomainObject;
use Evently\DomainObjects\ProductPriceDomainObject;
use Evently\Http\Actions\BaseAction;
use Evently\Repository\Eloquent\Value\Relationship;
use Evently\Repository\Interfaces\AttendeeRepositoryInterface;
use Evently\Resources\Attendee\AttendeeResourcePublic;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class GetAttendeeActionPublic extends BaseAction
{
    private AttendeeRepositoryInterface $attendeeRepository;

    public function __construct(AttendeeRepositoryInterface $attendeeRepository)
    {
        $this->attendeeRepository = $attendeeRepository;
    }

    /**
     * @todo move to handler
     */
    public function __invoke(int $eventId, string $attendeeShortId): JsonResponse|Response
    {
        $attendee = $this->attendeeRepository
            ->loadRelation(new Relationship(
                domainObject: ProductDomainObject::class,
                nested: [
                    new Relationship(
                        domainObject: ProductPriceDomainObject::class,
                    ),
                ], name: 'product'))
            ->findFirstWhere([
                AttendeeDomainObjectAbstract::SHORT_ID => $attendeeShortId
            ]);

        if (!$attendee) {
            return $this->notFoundResponse();
        }

        return $this->resourceResponse(AttendeeResourcePublic::class, $attendee);
    }
}
