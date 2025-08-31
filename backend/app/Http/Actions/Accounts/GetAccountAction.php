<?php

declare(strict_types=1);

namespace Evently\Http\Actions\Accounts;

use Evently\DomainObjects\AccountConfigurationDomainObject;
use Evently\DomainObjects\Enums\Role;
use Evently\Http\Actions\BaseAction;
use Evently\Repository\Eloquent\Value\Relationship;
use Evently\Repository\Interfaces\AccountRepositoryInterface;
use Evently\Resources\Account\AccountResource;
use Illuminate\Http\JsonResponse;

class GetAccountAction extends BaseAction
{
    protected AccountRepositoryInterface $accountRepository;

    public function __construct(AccountRepositoryInterface $accountRepository)
    {
        $this->accountRepository = $accountRepository;
    }

    public function __invoke(?int $accountId = null): JsonResponse
    {
        $this->minimumAllowedRole(Role::ORGANIZER);

        $account = $this->accountRepository
            ->loadRelation(new Relationship(
                domainObject: AccountConfigurationDomainObject::class,
                name: 'configuration',
            ))
            ->findById($this->getAuthenticatedAccountId());

        return $this->resourceResponse(AccountResource::class, $account);
    }
}
