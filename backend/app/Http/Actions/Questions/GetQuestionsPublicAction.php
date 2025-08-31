<?php

namespace Evently\Http\Actions\Questions;

use Evently\DomainObjects\Generated\QuestionDomainObjectAbstract;
use Evently\DomainObjects\ProductDomainObject;
use Evently\Http\Actions\BaseAction;
use Evently\Repository\Interfaces\QuestionRepositoryInterface;
use Evently\Resources\Question\QuestionResourcePublic;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GetQuestionsPublicAction extends BaseAction
{
    private QuestionRepositoryInterface $questionRepository;

    public function __construct(QuestionRepositoryInterface $questionRepository)
    {
        $this->questionRepository = $questionRepository;
    }

    public function __invoke(Request $request, int $eventId): JsonResponse
    {
        $questions = $this->questionRepository
            ->loadRelation(ProductDomainObject::class)
            ->findWhere([
                QuestionDomainObjectAbstract::EVENT_ID => $eventId,
                QuestionDomainObjectAbstract::IS_HIDDEN => false,
            ])
            ->sortBy(fn(QuestionDomainObjectAbstract $question) => $question->getOrder());

        return $this->resourceResponse(QuestionResourcePublic::class, $questions);
    }
}
