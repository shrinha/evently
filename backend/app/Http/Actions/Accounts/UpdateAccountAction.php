<?php

namespace Evently\Http\Actions\Accounts;

use Evently\DomainObjects\Enums\Role;
use Evently\Http\Actions\BaseAction;
use Evently\Http\Request\Account\UpdateAccountRequest;
use Evently\Resources\Account\AccountResource;
use Evently\Services\Application\Handlers\Account\DTO\UpdateAccountDTO;
use Evently\Services\Application\Handlers\Account\UpdateAccountHanlder;
use Illuminate\Http\JsonResponse;

class UpdateAccountAction extends BaseAction
{
    private UpdateAccountHanlder $updateAccountHandler;

    public function __construct(UpdateAccountHanlder $updateAccountHandler)
    {
        $this->updateAccountHandler = $updateAccountHandler;
    }

    public function __invoke(UpdateAccountRequest $request): JsonResponse
    {
        $this->minimumAllowedRole(Role::ADMIN);

        $authUser = $this->getAuthenticatedUser();

        $payload = array_merge($request->validated(), [
            'account_id' => $this->getAuthenticatedAccountId(),
            'updated_by_user_id' => $authUser->getId(),
        ]);

        $account = $this->updateAccountHandler->handle(UpdateAccountDTO::fromArray($payload));

        return $this->resourceResponse(AccountResource::class, $account);
    }
}
