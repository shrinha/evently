<?php

declare(strict_types=1);

namespace Evently\Repository\Interfaces;

use Evently\DomainObjects\ProductPriceDomainObject;
use Evently\Repository\Eloquent\BaseRepository;

/**
 * @extends BaseRepository<ProductPriceDomainObject>
 */
interface ProductPriceRepositoryInterface extends RepositoryInterface
{
}
