<?php

namespace Evently\DomainObjects\Interfaces;

interface IsFilterable
{
    /**
     * @return array<string>
     */
    public static function getAllowedFilterFields(): array;
}
