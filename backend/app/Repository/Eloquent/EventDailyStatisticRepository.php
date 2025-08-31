<?php

namespace Evently\Repository\Eloquent;

use Evently\DomainObjects\EventDailyStatisticDomainObject;
use Evently\Models\EventDailyStatistic;
use Evently\Repository\Interfaces\EventDailyStatisticRepositoryInterface;

class EventDailyStatisticRepository extends BaseRepository implements EventDailyStatisticRepositoryInterface
{
    protected function getModel(): string
    {
        return EventDailyStatistic::class;
    }

    public function getDomainObject(): string
    {
        return EventDailyStatisticDomainObject::class;
    }
}
