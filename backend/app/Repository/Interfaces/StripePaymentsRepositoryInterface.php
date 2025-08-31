<?php

namespace Evently\Repository\Interfaces;

use Evently\DomainObjects\StripePaymentDomainObject;
use Evently\Repository\Eloquent\BaseRepository;

/**
 * @extends BaseRepository<StripePaymentDomainObject>
 */
interface StripePaymentsRepositoryInterface extends RepositoryInterface
{
}
