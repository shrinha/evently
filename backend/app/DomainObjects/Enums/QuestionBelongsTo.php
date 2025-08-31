<?php

namespace Evently\DomainObjects\Enums;

enum QuestionBelongsTo
{
    use BaseEnum;

    case PRODUCT;
    case ORDER;
}
