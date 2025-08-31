<?php

namespace Evently\Repository\Eloquent;

use Evently\DomainObjects\AccountConfigurationDomainObject;
use Evently\Models\AccountConfiguration;
use Evently\Repository\Interfaces\AccountConfigurationRepositoryInterface;

class AccountConfigurationRepository extends BaseRepository implements AccountConfigurationRepositoryInterface
{
    protected function getModel(): string
    {
        return AccountConfiguration::class;
    }

    public function getDomainObject(): string
    {
        return AccountConfigurationDomainObject::class;
    }
}
