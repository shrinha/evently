<?php

namespace Evently\Http\Actions\Questions;

use Evently\DomainObjects\EventDomainObject;
use Evently\Http\Actions\BaseAction;
use Evently\Http\Request\Questions\UpsertQuestionRequest;
use Evently\Http\ResponseCodes;
use Evently\Resources\Question\QuestionResource;
use Evently\Services\Application\Handlers\Question\CreateQuestionHandler;
use Evently\Services\Application\Handlers\Question\DTO\UpsertQuestionDTO;
use Illuminate\Http\JsonResponse;

class CreateQuestionAction extends BaseAction
{
    private CreateQuestionHandler $createQuestionHandler;

    public function __construct(CreateQuestionHandler $createQuestionHandler)
    {
        $this->createQuestionHandler = $createQuestionHandler;
    }

    public function __invoke(UpsertQuestionRequest $request, int $eventId): JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        $question = $this->createQuestionHandler->handle(UpsertQuestionDTO::fromArray([
            'title' => $request->input('title'),
            'type' => $request->input('type'),
            'required' => $request->boolean('required'),
            'options' => $request->input('options'),
            'event_id' => $eventId,
            'product_ids' => $request->input('product_ids'),
            'belongs_to' => $request->input('belongs_to'),
            'is_hidden' => $request->boolean('is_hidden'),
            'description' => $request->input('description'),
        ]));

        return $this->resourceResponse(
            resource: QuestionResource::class,
            data: $question,
            statusCode: ResponseCodes::HTTP_CREATED
        );
    }
}
