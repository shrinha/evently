<?php

namespace Evently\Http\Actions\Questions;

use Evently\DomainObjects\EventDomainObject;
use Evently\DomainObjects\ProductDomainObject;
use Evently\Http\Actions\BaseAction;
use Evently\Repository\Interfaces\QuestionRepositoryInterface;
use Evently\Resources\Question\QuestionResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GetQuestionAction extends BaseAction
{
    private QuestionRepositoryInterface $questionRepository;

    public function __construct(QuestionRepositoryInterface $questionRepository)
    {
        $this->questionRepository = $questionRepository;
    }

    public function __invoke(Request $request, int $eventId, int $questionId): JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        $questions = $this->questionRepository
            ->loadRelation(ProductDomainObject::class)
            ->findById($questionId);

        return $this->resourceResponse(QuestionResource::class, $questions);
    }
}
