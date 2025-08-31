<?php

namespace Evently\Repository\Eloquent;


use Evently\DomainObjects\QuestionAnswerDomainObject;
use Evently\Models\QuestionAnswer;
use Evently\Repository\Interfaces\QuestionAnswerRepositoryInterface;

class QuestionAnswerRepository extends BaseRepository implements QuestionAnswerRepositoryInterface
{
    protected function getModel(): string
    {
        return QuestionAnswer::class;
    }

    public function getDomainObject(): string
    {
        return QuestionAnswerDomainObject::class;
    }
}
