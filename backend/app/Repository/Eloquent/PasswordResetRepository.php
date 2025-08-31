<?php

namespace Evently\Repository\Eloquent;

use Evently\DomainObjects\PasswordResetDomainObject;
use Evently\Models\PasswordReset;
use Evently\Repository\Interfaces\PasswordResetRepositoryInterface;

class PasswordResetRepository extends BaseRepository implements PasswordResetRepositoryInterface
{
    protected function getModel(): string
    {
        return PasswordReset::class;
    }

    public function getDomainObject(): string
    {
        return PasswordResetDomainObject::class;
    }
}
