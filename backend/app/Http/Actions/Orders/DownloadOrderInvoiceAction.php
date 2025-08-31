<?php

namespace Evently\Http\Actions\Orders;

use Evently\DomainObjects\EventDomainObject;
use Evently\Http\Actions\BaseAction;
use Evently\Services\Application\Handlers\Order\DTO\GetOrderInvoiceDTO;
use Evently\Services\Application\Handlers\Order\GetOrderInvoiceHandler;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DownloadOrderInvoiceAction extends BaseAction
{
    public function __construct(
        private readonly GetOrderInvoiceHandler $orderInvoiceHandler,
    )
    {
    }

    public function __invoke(Request $request, int $eventId, int $orderId): Response
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        $invoice = $this->orderInvoiceHandler->handle(new GetOrderInvoiceDTO(
            orderId: $orderId,
            eventId: $eventId,
        ));

        return $invoice->pdf->stream($invoice->filename);
    }
}
