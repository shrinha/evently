<?php

namespace Evently\Repository\Eloquent;

use Evently\DomainObjects\EventSettingDomainObject;
use Evently\Models\EventSetting;
use Evently\Repository\Interfaces\EventSettingsRepositoryInterface;

class EventSettingsRepository extends BaseRepository implements EventSettingsRepositoryInterface
{
    protected function getModel(): string
    {
        return EventSetting::class;
    }

    public function getDomainObject(): string
    {
        return EventSettingDomainObject::class;
    }
}
