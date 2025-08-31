<?php

namespace Evently\Services\Application\Handlers\Order\Public;

use Evently\Services\Domain\Order\DTO\InvoicePdfResponseDTO;
use Evently\Services\Domain\Order\GenerateOrderInvoicePDFService;

class DownloadOrderInvoicePublicHandler
{
    public function __construct(
        private readonly GenerateOrderInvoicePDFService $generateOrderInvoicePDFService,
    )
    {
    }

    public function handle(int $eventId, string $orderShortId): InvoicePdfResponseDTO
    {
        return $this->generateOrderInvoicePDFService->generatePdfFromOrderShortId(
            orderShortId: $orderShortId,
            eventId: $eventId,
        );
    }
}
