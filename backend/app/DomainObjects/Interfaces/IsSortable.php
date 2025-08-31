<?php

namespace Evently\DomainObjects\Interfaces;

use Evently\DomainObjects\SortingAndFiltering\AllowedSorts;

interface IsSortable
{
    /**
     * The default sort column
     *
     * @return string
     */
    public static function getDefaultSort(): string;

    /**
     * The default sort order - asc or desc
     *
     * @return string
     */
    public static function getDefaultSortDirection(): string;

    /**
     * @return AllowedSorts
     */
    public static function getAllowedSorts(): AllowedSorts;
}
