<?php

namespace Evently\Jobs\Event;

use Evently\Exceptions\UnableToSendMessageException;
use Evently\Services\Application\Handlers\Message\DTO\SendMessageDTO;
use Evently\Services\Domain\Mail\SendEventEmailMessagesService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendMessagesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private SendMessageDTO $messageData;

    public function __construct(SendMessageDTO $messageData)
    {
        $this->messageData = $messageData;
    }

    /**
     * @throws UnableToSendMessageException
     */
    public function handle(SendEventEmailMessagesService $emailMessagesService): void
    {
        $emailMessagesService->send($this->messageData);
    }
}
