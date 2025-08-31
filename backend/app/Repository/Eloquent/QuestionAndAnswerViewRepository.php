<?php

namespace Evently\Repository\Eloquent;

use Evently\DomainObjects\QuestionAndAnswerViewDomainObject;
use Evently\Models\QuestionAndAnswerView;
use Evently\Repository\Interfaces\QuestionAndAnswerViewRepositoryInterface;

class QuestionAndAnswerViewRepository extends BaseRepository implements QuestionAndAnswerViewRepositoryInterface
{
    protected function getModel(): string
    {
        return QuestionAndAnswerView::class;
    }

    public function getDomainObject(): string
    {
        return QuestionAndAnswerViewDomainObject::class;
    }
}
