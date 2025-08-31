<?php

namespace Evently\Http\Actions\Users;

use Evently\DomainObjects\UserDomainObject;
use Evently\Exceptions\ResourceConflictException;
use Evently\Http\Actions\BaseAction;
use Evently\Resources\User\UserResource;
use Evently\Services\Application\Handlers\User\ConfirmEmailChangeHandler;
use Evently\Services\Application\Handlers\User\DTO\ConfirmEmailChangeDTO;
use Evently\Services\Infrastructure\Encryption\Exception\DecryptionFailedException;
use Evently\Services\Infrastructure\Encryption\Exception\EncryptedPayloadExpiredException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as HttpCodes;
use Throwable;

class ConfirmEmailChangeAction extends BaseAction
{
    private ConfirmEmailChangeHandler $confirmEmailChangeHandler;

    public function __construct(ConfirmEmailChangeHandler $confirmEmailChangeHandler)
    {
        $this->confirmEmailChangeHandler = $confirmEmailChangeHandler;
    }

    /**
     * @throws DecryptionFailedException|Throwable
     */
    public function __invoke(int $userId, string $changeToken): Response|JsonResponse
    {
        $this->isActionAuthorized($userId, UserDomainObject::class);

        try {
            $user = $this->confirmEmailChangeHandler->handle(new ConfirmEmailChangeDTO(
                token: $changeToken,
                accountId: $this->getAuthenticatedAccountId(),
            ));
        } catch (EncryptedPayloadExpiredException) {
            return $this->notFoundResponse();
        } catch (ResourceConflictException $exception) {
            return $this->errorResponse($exception->getMessage(), HttpCodes::HTTP_CONFLICT);
        }

        return $this->resourceResponse(UserResource::class, $user);
    }
}
