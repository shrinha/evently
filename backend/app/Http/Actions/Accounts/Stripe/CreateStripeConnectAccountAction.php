<?php

namespace Evently\Http\Actions\Accounts\Stripe;

use Evently\DomainObjects\AccountDomainObject;
use Evently\DomainObjects\Enums\Role;
use Evently\Exceptions\CreateStripeConnectAccountFailedException;
use Evently\Exceptions\CreateStripeConnectAccountLinksFailedException;
use Evently\Exceptions\SaasModeEnabledException;
use Evently\Http\Actions\BaseAction;
use Evently\Resources\Account\Stripe\StripeConnectAccountResponseResource;
use Evently\Services\Application\Handlers\Account\Payment\Stripe\CreateStripeConnectAccountHandler;
use Evently\Services\Application\Handlers\Account\Payment\Stripe\DTO\CreateStripeConnectAccountDTO;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class CreateStripeConnectAccountAction extends BaseAction
{
    public function __construct(
        private readonly CreateStripeConnectAccountHandler $createStripeConnectAccountHandler,
    )
    {
    }

    /**
     * @throws Throwable
     */
    public function __invoke(int $accountId): JsonResponse
    {
        $this->isActionAuthorized($accountId, AccountDomainObject::class, Role::ADMIN);

        try {
            $accountResult = $this->createStripeConnectAccountHandler->handle(CreateStripeConnectAccountDTO::fromArray([
                'accountId' => $this->getAuthenticatedAccountId(),
            ]));
        } catch (CreateStripeConnectAccountLinksFailedException|CreateStripeConnectAccountFailedException $e) {
            return $this->errorResponse(
                message: $e->getMessage(),
                statusCode: Response::HTTP_INTERNAL_SERVER_ERROR
            );
        } catch (SaasModeEnabledException $e) {
            return $this->errorResponse(
                message: $e->getMessage(),
                statusCode: Response::HTTP_FORBIDDEN
            );
        }

        return $this->resourceResponse(
            resource: StripeConnectAccountResponseResource::class,
            data: $accountResult
        );
    }
}
