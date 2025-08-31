<?php

namespace Evently\Http\Actions\Events;

use Evently\Exceptions\OrganizerNotFoundException;
use Evently\Http\Actions\BaseAction;
use Evently\Http\Request\Event\CreateEventRequest;
use Evently\Resources\Event\EventResource;
use Evently\Services\Application\Handlers\Event\CreateEventHandler;
use Evently\Services\Application\Handlers\Event\DTO\CreateEventDTO;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Throwable;

class CreateEventAction extends BaseAction
{
    public function __construct(
        private readonly CreateEventHandler $createEventHandler
    )
    {
    }

    /**
     * @throws ValidationException|Throwable
     */
    public function __invoke(CreateEventRequest $request): JsonResponse
    {
        $authorisedUser = $this->getAuthenticatedUser();

        $eventData = array_merge(
            $request->validated(),
            [
                'account_id' => $this->getAuthenticatedAccountId(),
                'user_id' => $authorisedUser->getId(),
            ]
        );

        try {
            $event = $this->createEventHandler->handle(
                eventData: CreateEventDTO::fromArray($eventData)
            );
        } catch (OrganizerNotFoundException $e) {
            throw ValidationException::withMessages([
                'organizer_id' => $e->getMessage(),
            ]);
        }

        return $this->resourceResponse(EventResource::class, $event);
    }
}
