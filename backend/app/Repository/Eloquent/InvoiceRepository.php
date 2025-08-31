<?php

namespace Evently\Repository\Eloquent;

use Evently\DomainObjects\InvoiceDomainObject;
use Evently\Models\Invoice;
use Evently\Repository\Interfaces\InvoiceRepositoryInterface;

class InvoiceRepository extends BaseRepository implements InvoiceRepositoryInterface
{
    protected function getModel(): string
    {
        return Invoice::class;
    }

    public function getDomainObject(): string
    {
        return InvoiceDomainObject::class;
    }

    public function findLatestInvoiceForEvent(int $eventId): ?InvoiceDomainObject
    {
        $invoice =  $this->model
            ->whereHas('order', function ($query) use ($eventId) {
                $query->where('event_id', $eventId);
            })
            ->orderBy('id', 'desc')
            ->first();

        return $this->handleSingleResult($invoice);
    }

    public function findLatestInvoiceForOrder(int $orderId): ?InvoiceDomainObject
    {
        $invoice =  $this->model
            ->where('order_id', $orderId)
            ->orderBy('id', 'desc')
            ->first();

        return $this->handleSingleResult($invoice);
    }
}
