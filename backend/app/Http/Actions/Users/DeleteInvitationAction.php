<?php

namespace Evently\Http\Actions\Users;

use Evently\DomainObjects\Enums\Role;
use Evently\DomainObjects\Status\UserStatus;
use Evently\DomainObjects\UserDomainObject;
use Evently\Http\Actions\BaseAction;
use Evently\Repository\Interfaces\UserRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class DeleteInvitationAction extends BaseAction
{
    private UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function __invoke(int $userId): JsonResponse|Response
    {
        $this->isActionAuthorized($userId, UserDomainObject::class, Role::ADMIN);

        $user = $this->userRepository->findByIdAndAccountId($userId, $this->getAuthenticatedAccountId());

        if ($user->getCurrentAccountUser()?->getStatus() !== UserStatus::INVITED->name) {
            return $this->errorResponse(__('No invitation found for this user.'));
        }

        $this->userRepository->deleteWhere([
            'id' => $userId,
        ]);

        return $this->noContentResponse();
    }
}
