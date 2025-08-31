<?php

namespace Evently\Http\Actions\Questions;

use Evently\DomainObjects\EventDomainObject;
use Evently\Exceptions\CannotDeleteEntityException;
use Evently\Http\Actions\BaseAction;
use Evently\Services\Application\Handlers\Question\DeleteQuestionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Throwable;

class DeleteQuestionAction extends BaseAction
{
    private DeleteQuestionHandler $deleteQuestionHandler;

    public function __construct(DeleteQuestionHandler $deleteQuestionHandler)
    {
        $this->deleteQuestionHandler = $deleteQuestionHandler;
    }

    /**
     * @throws Throwable
     * @throws CannotDeleteEntityException
     */
    public function __invoke(int $eventId, int $questionId): Response|JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        try {
            $this->deleteQuestionHandler->handle($questionId, $eventId);
        } catch (CannotDeleteEntityException $exception) {
            return $this->errorResponse($exception->getMessage());
        }

        return $this->deletedResponse();
    }
}
