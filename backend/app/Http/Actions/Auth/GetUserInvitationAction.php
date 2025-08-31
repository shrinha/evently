<?php

namespace Evently\Http\Actions\Auth;

use Evently\Http\Actions\BaseAction;
use Evently\Http\ResponseCodes;
use Evently\Repository\Interfaces\UserRepositoryInterface;
use Evently\Resources\User\UserResource;
use Evently\Services\Infrastructure\Encryption\EncryptedPayloadService;
use Evently\Services\Infrastructure\Encryption\Exception\DecryptionFailedException;
use Evently\Services\Infrastructure\Encryption\Exception\EncryptedPayloadExpiredException;
use Illuminate\Http\JsonResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

/**
 * @todo move to a service
 */
class GetUserInvitationAction extends BaseAction
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly EncryptedPayloadService $encryptedPayloadService,
        private readonly LoggerInterface         $logger,
    )
    {
    }

    public function __invoke(string $inviteToken): JsonResponse
    {
        try {
            [
                'user_id' => $userId,
                'account_id' => $accountId,
            ] = $this->encryptedPayloadService->decryptPayload($inviteToken);

        } catch (EncryptedPayloadExpiredException) {
            throw new HttpException(ResponseCodes::HTTP_GONE, __('The invitation has expired'));
        } catch (DecryptionFailedException) {
            throw new HttpException(ResponseCodes::HTTP_BAD_REQUEST, __('The invitation is invalid'));
        }

        try {
            $user = $this->userRepository->findByIdAndAccountId($userId, $accountId);
        } catch (ResourceNotFoundException) {
            $this->logger->info(__('Invitation valid, but user not found'), [
                'user_id' => $userId,
                'account_id' => $accountId,
            ]);

            throw new HttpException(
                statusCode: ResponseCodes::HTTP_NOT_FOUND,
                message: __('No user found for this invitation. The invitation may have been revoked.'),
            );
        }

        return $this->resourceResponse(UserResource::class, $user);
    }
}
