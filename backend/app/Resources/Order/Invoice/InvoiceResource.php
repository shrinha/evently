<?php

namespace Evently\Resources\Order\Invoice;

use Evently\DomainObjects\InvoiceDomainObject;
use Evently\Resources\BaseResource;

/** @mixin InvoiceDomainObject */
class InvoiceResource extends BaseResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->getId(),
            'invoice_number' => $this->getInvoiceNumber(),
            'order_id' => $this->getOrderId(),
            'status' => $this->getStatus(),
        ];
    }
}
