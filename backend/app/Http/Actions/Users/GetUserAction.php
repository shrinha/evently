<?php

declare(strict_types=1);

namespace Evently\Http\Actions\Users;

use Evently\DomainObjects\Enums\Role;
use Evently\Http\Actions\BaseAction;
use Evently\Repository\Interfaces\UserRepositoryInterface;
use Evently\Resources\User\UserResource;
use Illuminate\Http\JsonResponse;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class GetUserAction extends BaseAction
{
    private UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function __invoke(int $userId): JsonResponse
    {
        $this->minimumAllowedRole(Role::ADMIN);

        $user = $this->userRepository->findByIdAndAccountId($userId, $this->getAuthenticatedAccountId());

        if (!$user) {
            throw new ResourceNotFoundException();
        }

        return $this->resourceResponse(
            resource: UserResource::class,
            data: $user
        );
    }
}
