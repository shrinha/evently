<?php

namespace Evently\Repository\Eloquent;

use Evently\DomainObjects\OrganizerSettingDomainObject;
use Evently\Models\OrganizerSetting;
use Evently\Repository\Interfaces\OrganizerSettingsRepositoryInterface;

class OrganizerSettingsRepository extends BaseRepository implements OrganizerSettingsRepositoryInterface
{
    protected function getModel(): string
    {
        return OrganizerSetting::class;
    }

    public function getDomainObject(): string
    {
        return OrganizerSettingDomainObject::class;
    }
}
