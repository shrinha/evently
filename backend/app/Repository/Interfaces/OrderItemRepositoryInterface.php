<?php

namespace Evently\Repository\Interfaces;

use Evently\DomainObjects\OrderItemDomainObject;
use Evently\Repository\Eloquent\BaseRepository;

/**
 * @extends BaseRepository<OrderItemDomainObject>
 */
interface OrderItemRepositoryInterface extends RepositoryInterface
{
}
