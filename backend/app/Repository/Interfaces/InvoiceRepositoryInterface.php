<?php

namespace Evently\Repository\Interfaces;

use Evently\DomainObjects\InvoiceDomainObject;
use Evently\Repository\Eloquent\BaseRepository;

/**
 * @extends BaseRepository<InvoiceDomainObject>
 */
interface InvoiceRepositoryInterface extends RepositoryInterface
{
    public function findLatestInvoiceForEvent(int $eventId): ?InvoiceDomainObject;

    public function findLatestInvoiceForOrder(int $orderId): ?InvoiceDomainObject;
}
