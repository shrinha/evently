<?php

namespace Evently\DomainObjects\SortingAndFiltering;

class AllowedSort
{
    public function __construct(
        public string $key,
        public ?string $ascDescription,
        public ?string $descDescription,
    )
    {
    }
}
