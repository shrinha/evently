<?php

namespace Evently\Repository\Eloquent;

use Evently\DomainObjects\PasswordResetTokenDomainObject;
use Evently\Models\PasswordResetToken;
use Evently\Repository\Interfaces\PasswordResetTokenRepositoryInterface;

class PasswordResetTokenRepository extends BaseRepository implements PasswordResetTokenRepositoryInterface
{
    protected function getModel(): string
    {
        return PasswordResetToken::class;
    }

    public function getDomainObject(): string
    {
        return PasswordResetTokenDomainObject::class;
    }
}
