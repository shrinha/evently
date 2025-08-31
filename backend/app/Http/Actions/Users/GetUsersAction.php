<?php

declare(strict_types=1);

namespace Evently\Http\Actions\Users;

use Evently\DomainObjects\AccountUserDomainObject;
use Evently\DomainObjects\Enums\Role;
use Evently\Http\Actions\BaseAction;
use Evently\Repository\Eloquent\Value\Relationship;
use Evently\Repository\Interfaces\UserRepositoryInterface;
use Evently\Resources\User\UserResource;
use Illuminate\Http\JsonResponse;

class GetUsersAction extends BaseAction
{
    private UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function __invoke(): JsonResponse
    {
        $this->minimumAllowedRole(Role::ADMIN);

        return $this->resourceResponse(
            UserResource::class,
            $this->userRepository
                ->loadRelation(new Relationship(domainObject: AccountUserDomainObject::class, name: 'currentAccountUser'))
                ->findUsersByAccountId($this->getAuthenticatedAccountId()),
        );
    }
}
