<?php

namespace Evently\Http\Actions\Messages;

use Evently\DomainObjects\EventDomainObject;
use Evently\DomainObjects\MessageDomainObject;
use Evently\DomainObjects\UserDomainObject;
use Evently\Http\Actions\BaseAction;
use Evently\Http\DTO\QueryParamsDTO;
use Evently\Repository\Eloquent\Value\Relationship;
use Evently\Repository\Interfaces\MessageRepositoryInterface;
use Evently\Resources\Message\MessageResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GetMessagesAction extends BaseAction
{
    private MessageRepositoryInterface $messageRepository;

    public function __construct(MessageRepositoryInterface $MessageRepository)
    {
        $this->messageRepository = $MessageRepository;
    }

    public function __invoke(Request $request, int $eventId): JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        $messages = $this->messageRepository
            ->loadRelation(new Relationship(UserDomainObject::class, name: 'sent_by_user'))
            ->findByEventId($eventId, QueryParamsDTO::fromArray($request->query->all()));

        return $this->filterableResourceResponse(
            resource: MessageResource::class,
            data: $messages,
            domainObject: MessageDomainObject::class
        );
    }
}
