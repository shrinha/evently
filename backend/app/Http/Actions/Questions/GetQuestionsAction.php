<?php

namespace Evently\Http\Actions\Questions;

use Evently\DomainObjects\EventDomainObject;
use Evently\DomainObjects\ProductDomainObject;
use Evently\DomainObjects\ProductPriceDomainObject;
use Evently\Http\Actions\BaseAction;
use Evently\Repository\Eloquent\Value\Relationship;
use Evently\Repository\Interfaces\QuestionRepositoryInterface;
use Evently\Resources\Question\QuestionResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GetQuestionsAction extends BaseAction
{
    private QuestionRepositoryInterface $questionRepository;

    public function __construct(QuestionRepositoryInterface $questionRepository)
    {
        $this->questionRepository = $questionRepository;
    }

    public function __invoke(Request $request, int $eventId): JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        $questions = $this->questionRepository
            ->loadRelation(
                new Relationship(ProductDomainObject::class, [
                    new Relationship(ProductPriceDomainObject::class)
                ])
            )
            ->findByEventId($eventId);

        return $this->resourceResponse(QuestionResource::class, $questions);
    }
}
