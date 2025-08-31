<?php

namespace Evently\Http\Actions\Auth;

use Evently\Exceptions\ResourceConflictException;
use Evently\Http\Actions\BaseAction;
use Evently\Http\Request\Auth\AcceptInvitationRequest;
use Evently\Http\ResponseCodes;
use Evently\Services\Application\Handlers\Auth\AcceptInvitationHandler;
use Evently\Services\Application\Handlers\Auth\DTO\AcceptInvitationDTO;
use Evently\Services\Infrastructure\Encryption\Exception\DecryptionFailedException;
use Evently\Services\Infrastructure\Encryption\Exception\EncryptedPayloadExpiredException;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class AcceptInvitationAction extends BaseAction
{
    public function __construct(private readonly AcceptInvitationHandler $handler)
    {
    }

    public function __invoke(AcceptInvitationRequest $request, string $inviteToken): Response
    {
        try {
            $this->handler->handle(AcceptInvitationDTO::fromArray($request->validated() + ['invitation_token' => $inviteToken]));
        } catch (ResourceConflictException $e) {
            throw new HttpException(ResponseCodes::HTTP_CONFLICT, $e->getMessage());
        } catch (DecryptionFailedException|EncryptedPayloadExpiredException $e) {
            throw new HttpException(ResponseCodes::HTTP_BAD_REQUEST, $e->getMessage());
        } catch (ResourceNotFoundException $e) {
            throw new HttpException(ResponseCodes::HTTP_NOT_FOUND, $e->getMessage());
        }

        return $this->noContentResponse();
    }
}
