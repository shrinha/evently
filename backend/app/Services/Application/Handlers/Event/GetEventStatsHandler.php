<?php

namespace Evently\Services\Application\Handlers\Event;

use Evently\Services\Application\Handlers\Event\DTO\EventStatsRequestDTO;
use Evently\Services\Application\Handlers\Event\DTO\EventStatsResponseDTO;
use Evently\Services\Domain\Event\EventStatsFetchService;

readonly class GetEventStatsHandler
{
    public function __construct(private EventStatsFetchService $eventStatsFetchService)
    {
    }

    public function handle(EventStatsRequestDTO $statsRequestDTO): EventStatsResponseDTO
    {
        return $this->eventStatsFetchService->getEventStats($statsRequestDTO);
    }
}
