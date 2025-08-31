<?php

namespace Evently\Services\Application\Handlers\CheckInList\Public;

use Evently\Exceptions\CannotCheckInException;
use Evently\Services\Application\Handlers\CheckInList\Public\DTO\DeleteAttendeeCheckInPublicDTO;
use Evently\Services\Domain\CheckInList\DeleteAttendeeCheckInService;
use Evently\Services\Infrastructure\DomainEvents\DomainEventDispatcherService;
use Evently\Services\Infrastructure\DomainEvents\Enums\DomainEventType;
use Evently\Services\Infrastructure\DomainEvents\Events\CheckinEvent;
use Illuminate\Database\DatabaseManager;
use Psr\Log\LoggerInterface;
use Throwable;

class DeleteAttendeeCheckInPublicHandler
{
    public function __construct(
        private readonly DeleteAttendeeCheckInService $deleteAttendeeCheckInService,
        private readonly LoggerInterface              $logger,
        private readonly DomainEventDispatcherService $domainEventDispatcherService,
        private readonly DatabaseManager              $databaseManager
    )
    {
    }

    /**
     * @throws CannotCheckInException
     * @throws Throwable
     */
    public function handle(DeleteAttendeeCheckInPublicDTO $checkInData): void
    {
        $this->databaseManager->transaction(function () use ($checkInData) {
            $deletedCheckInId = $this->deleteAttendeeCheckInService->deleteAttendeeCheckIn(
                $checkInData->checkInListShortId,
                $checkInData->checkInShortId,
            );

            $this->logger->info('Attendee check-in deleted', [
                'check_in_list_uuid' => $checkInData->checkInListShortId,
                'attendee_public_id' => $checkInData->checkInShortId,
                'check_in_user_ip_address' => $checkInData->checkInUserIpAddress,
            ]);

            $this->domainEventDispatcherService->dispatch(
                new CheckinEvent(
                    type: DomainEventType::CHECKIN_DELETED,
                    attendeeCheckinId: $deletedCheckInId,
                )
            );
        });
    }
}
