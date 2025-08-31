<?php

namespace Evently\Repository\Interfaces;

use Evently\DomainObjects\PasswordResetTokenDomainObject;
use Evently\Repository\Eloquent\BaseRepository;

/**
 * @extends BaseRepository<PasswordResetTokenDomainObject>
 */
interface PasswordResetTokenRepositoryInterface extends RepositoryInterface
{

}
