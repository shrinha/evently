<?php

namespace Evently\Services\Application\Handlers\Order;

use Evently\Services\Application\Handlers\Order\DTO\GetOrderInvoiceDTO;
use Evently\Services\Domain\Order\DTO\InvoicePdfResponseDTO;
use Evently\Services\Domain\Order\GenerateOrderInvoicePDFService;

class GetOrderInvoiceHandler
{
    public function __construct(
        private readonly GenerateOrderInvoicePDFService $generateOrderInvoicePDFService,
    )
    {
    }

    public function handle(GetOrderInvoiceDTO $command): InvoicePdfResponseDTO
    {
        return $this->generateOrderInvoicePDFService->generatePdfFromOrderId(
            orderId: $command->orderId,
            eventId: $command->eventId,
        );
    }
}
