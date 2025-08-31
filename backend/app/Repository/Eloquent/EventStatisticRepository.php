<?php

namespace Evently\Repository\Eloquent;

use Evently\DomainObjects\EventStatisticDomainObject;
use Evently\Models\EventStatistic;
use Evently\Repository\Interfaces\EventStatisticRepositoryInterface;

class EventStatisticRepository extends BaseRepository implements EventStatisticRepositoryInterface
{
    protected function getModel(): string
    {
        return EventStatistic::class;
    }

    public function getDomainObject(): string
    {
        return EventStatisticDomainObject::class;
    }
}
