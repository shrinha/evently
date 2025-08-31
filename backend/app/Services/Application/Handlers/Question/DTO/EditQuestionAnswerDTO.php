<?php

namespace Evently\Services\Application\Handlers\Question\DTO;

use Evently\DataTransferObjects\BaseDTO;

class EditQuestionAnswerDTO extends BaseDTO
{
    public function __construct(
        public int               $questionAnswerId,
        public int               $eventId,
        public null|array|string $answer,
    )
    {
    }
}
