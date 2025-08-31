<?php

namespace Evently\Listeners\Order;

use Evently\DomainObjects\Status\OrderStatus;
use Evently\Events\OrderStatusChangedEvent;
use Evently\Exceptions\ResourceConflictException;
use Evently\Services\Domain\Invoice\InvoiceCreateService;

class CreateInvoiceListener
{
    public function __construct(private readonly InvoiceCreateService $invoiceCreateService)
    {
    }

    /**
     * @throws ResourceConflictException
     */
    public function handle(OrderStatusChangedEvent $event): void
    {
        if (!$event->createInvoice) {
            return;
        }

        $order = $event->order;

        if ($order->getStatus() !== OrderStatus::AWAITING_OFFLINE_PAYMENT->name && $order->getStatus() !== OrderStatus::COMPLETED->name) {
            return;
        }

        $this->invoiceCreateService->createInvoiceForOrder($order->getId());
    }
}
