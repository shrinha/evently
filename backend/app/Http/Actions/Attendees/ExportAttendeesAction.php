<?php

namespace Evently\Http\Actions\Attendees;

use Evently\DomainObjects\AttendeeCheckInDomainObject;
use Evently\DomainObjects\Enums\QuestionBelongsTo;
use Evently\DomainObjects\EventDomainObject;
use Evently\DomainObjects\ProductDomainObject;
use Evently\DomainObjects\ProductPriceDomainObject;
use Evently\DomainObjects\QuestionAndAnswerViewDomainObject;
use Evently\Exports\AttendeesExport;
use Evently\Http\Actions\BaseAction;
use Evently\Repository\Eloquent\Value\Relationship;
use Evently\Repository\Interfaces\AttendeeRepositoryInterface;
use Evently\Repository\Interfaces\QuestionRepositoryInterface;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportAttendeesAction extends BaseAction
{
    public function __construct(
        private readonly AttendeesExport             $export,
        private readonly AttendeeRepositoryInterface $attendeeRepository,
        private readonly QuestionRepositoryInterface $questionRepository
    )
    {
    }

    /**
     * @todo This should be passed off to a queue and moved to a service
     */
    public function __invoke(int $eventId): BinaryFileResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        $attendees = $this->attendeeRepository
            ->loadRelation(QuestionAndAnswerViewDomainObject::class)
            ->loadRelation(new Relationship(
                domainObject: AttendeeCheckInDomainObject::class,
                name: 'check_in',
            ))
            ->loadRelation(new Relationship(
                domainObject: ProductDomainObject::class,
                nested: [
                    new Relationship(
                        domainObject: ProductPriceDomainObject::class,
                    ),
                ],
                name: 'product'
            ))
            ->findByEventIdForExport($eventId);

        $questions = $this->questionRepository->findWhere([
            'event_id' => $eventId,
            'belongs_to' => QuestionBelongsTo::PRODUCT->name,
        ]);

        return Excel::download(
            $this->export->withData($attendees, $questions),
            'attendees.xlsx'
        );
    }
}
