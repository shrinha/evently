<?php

namespace Evently\Http\Actions\Users;

use Evently\DomainObjects\UserDomainObject;
use Evently\Http\Actions\BaseAction;
use Evently\Resources\User\UserResource;
use Evently\Services\Application\Handlers\User\CancelEmailChangeHandler;
use Evently\Services\Application\Handlers\User\DTO\CancelEmailChangeDTO;
use Illuminate\Http\JsonResponse;

class CancelEmailChangeAction extends BaseAction
{
    private CancelEmailChangeHandler $cancelEmailChangeHandler;

    public function __construct(CancelEmailChangeHandler $cancelEmailChangeHandler)
    {
        $this->cancelEmailChangeHandler = $cancelEmailChangeHandler;
    }

    public function __invoke(int $userId): JsonResponse
    {
        $this->isActionAuthorized($userId, UserDomainObject::class);

        $user = $this->cancelEmailChangeHandler->handle(
            new CancelEmailChangeDTO(
                userId: $userId,
                accountId: $this->getAuthenticatedAccountId(),
            )
        );

        return $this->resourceResponse(UserResource::class, $user);
    }
}
