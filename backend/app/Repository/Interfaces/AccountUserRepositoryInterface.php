<?php

declare(strict_types=1);

namespace Evently\Repository\Interfaces;

use Evently\DomainObjects\AccountUserDomainObject;
use Evently\Repository\Eloquent\BaseRepository;

/**
 * @extends BaseRepository<AccountUserDomainObject>
 */
interface AccountUserRepositoryInterface extends RepositoryInterface
{
}
