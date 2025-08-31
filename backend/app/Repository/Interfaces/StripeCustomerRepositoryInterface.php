<?php

namespace Evently\Repository\Interfaces;

use Evently\DomainObjects\StripeCustomerDomainObject;
use Evently\Repository\Eloquent\BaseRepository;

/**
 * @extends BaseRepository<StripeCustomerDomainObject>
 */
interface StripeCustomerRepositoryInterface extends RepositoryInterface
{

}
