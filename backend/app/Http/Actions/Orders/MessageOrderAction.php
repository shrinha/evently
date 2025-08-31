<?php

namespace Evently\Http\Actions\Orders;

use Evently\DomainObjects\EventDomainObject;
use Evently\Http\Actions\BaseAction;
use Evently\Http\Request\Message\SendMessageRequest;
use Evently\Jobs\Event\SendMessagesJob;
use Illuminate\Http\Response;

class MessageOrderAction extends BaseAction
{
    public function __invoke(SendMessageRequest $request, int $eventId, int $orderId): Response
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        SendMessagesJob::dispatch($orderId, $request->input('subject'), $request->input('message'));

        return $this->noContentResponse();
    }
}
