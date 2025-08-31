<?php

namespace Evently\Services\Application\Handlers\Question\DTO;

use Evently\DataTransferObjects\BaseDTO;
use Evently\DomainObjects\Enums\QuestionBelongsTo;
use Evently\DomainObjects\Enums\QuestionTypeEnum;

class UpsertQuestionDTO extends BaseDTO
{
    public function __construct(
        public string            $title,
        public QuestionTypeEnum  $type,
        public bool              $required,
        public ?array            $options,
        public int               $event_id,
        public array             $product_ids,
        public bool              $is_hidden,
        public QuestionBelongsTo $belongs_to,
        public ?string           $description = null,
    )
    {
    }
}
