<?php

namespace Evently\Services\Application\Handlers\Question;

use Evently\DomainObjects\QuestionDomainObject;
use Evently\Services\Application\Handlers\Question\DTO\UpsertQuestionDTO;
use Evently\Services\Domain\Question\CreateQuestionService;
use Evently\Services\Infrastructure\HtmlPurifier\HtmlPurifierService;
use Throwable;

class CreateQuestionHandler
{
    public function __construct(
        private readonly CreateQuestionService $createQuestionService,
        private readonly HtmlPurifierService   $purifier,
    )
    {
    }

    /**
     * @throws Throwable
     */
    public function handle(UpsertQuestionDTO $createQuestionDTO): QuestionDomainObject
    {
        $question = (new QuestionDomainObject())
            ->setTitle($createQuestionDTO->title)
            ->setEventId($createQuestionDTO->event_id)
            ->setBelongsTo($createQuestionDTO->belongs_to->name)
            ->setType($createQuestionDTO->type->name)
            ->setRequired($createQuestionDTO->required)
            ->setOptions($createQuestionDTO->options)
            ->setIsHidden($createQuestionDTO->is_hidden)
            ->setDescription($this->purifier->purify($createQuestionDTO->description));

        return $this->createQuestionService->createQuestion(
            $question,
            $createQuestionDTO->product_ids,
        );
    }
}
