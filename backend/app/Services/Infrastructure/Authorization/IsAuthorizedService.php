<?php

namespace Evently\Services\Infrastructure\Authorization;

use Evently\DomainObjects\AccountDomainObject;
use Evently\DomainObjects\Enums\Role;
use Evently\DomainObjects\EventDomainObject;
use Evently\DomainObjects\ImageDomainObject;
use Evently\DomainObjects\OrganizerDomainObject;
use Evently\DomainObjects\Status\UserStatus;
use Evently\DomainObjects\TaxAndFeesDomainObject;
use Evently\DomainObjects\UserDomainObject;
use Evently\Exceptions\UnauthorizedException;
use Evently\Repository\Interfaces\AccountRepositoryInterface;
use Evently\Repository\Interfaces\AccountUserRepositoryInterface;
use Evently\Repository\Interfaces\EventRepositoryInterface;
use Evently\Repository\Interfaces\ImageRepositoryInterface;
use Evently\Repository\Interfaces\OrganizerRepositoryInterface;
use Evently\Repository\Interfaces\TaxAndFeeRepositoryInterface;
use Evently\Repository\Interfaces\UserRepositoryInterface;
use Illuminate\Auth\AuthManager;
use Illuminate\Foundation\Application;

readonly class IsAuthorizedService
{
    public function __construct(
        private Application                    $app,
        private AccountUserRepositoryInterface $accountUserRepository,
        private AuthManager                    $auth,
    )
    {
    }

    /**
     * @todo This is a very simplistic way of handling roles. Currently we have an ADMIN and ORGANIZER role, but we
     *      will have a more granular approach to roles.
     */
    public function validateUserRole(Role $minimumRole, UserDomainObject $authUser): void
    {
        if ($minimumRole === Role::ADMIN && $authUser->getCurrentAccountUser()->getRole() !== Role::ADMIN->name) {
            throw new UnauthorizedException(__('You are not authorized to perform this action.'));
        }
    }

    public function isActionAuthorized(
        int              $entityId,
        string           $entityType,
        UserDomainObject $authUser,
        int              $authAccountId,
        Role             $minimumRole
    ): void
    {
        $this->validateUserStatus($authUser);
        $this->validateUserRole($minimumRole, $authUser);

        $repository = match ($entityType) {
            EventDomainObject::class => $this->app->make(EventRepositoryInterface::class),
            AccountDomainObject::class => $this->app->make(AccountRepositoryInterface::class),
            UserDomainObject::class => $this->app->make(UserRepositoryInterface::class),
            TaxAndFeesDomainObject::class => $this->app->make(TaxAndFeeRepositoryInterface::class),
            OrganizerDomainObject::class => $this->app->make(OrganizerRepositoryInterface::class),
            ImageDomainObject::class => $this->app->make(ImageRepositoryInterface::class),
        };

        $entity = $repository->findById($entityId);

        $result = match ($entityType) {
            EventDomainObject::class,
            ImageDomainObject::class,
            OrganizerDomainObject::class => $entity?->getAccountId() === $authAccountId,
            AccountDomainObject::class => $entity?->getId() === $authAccountId,
            UserDomainObject::class => $this->validateUserUpdate($entity, $authAccountId),
            TaxAndFeesDomainObject::class => $this->validateTax($entity, $authAccountId),
        };

        if (!$result) {
            throw new UnauthorizedException();
        }
    }

    private function validateUserUpdate(?UserDomainObject $user, int $authAccountId): bool
    {
        if ($user === null) {
            return false;
        }

        $accountUser = $this->accountUserRepository->findFirstWhere([
            'account_id' => $authAccountId,
            'user_id' => $user->getId(),
        ]);

        return $accountUser !== null;
    }

    private function validateTax(?TaxAndFeesDomainObject $taxOrFee, int $authAccountId): bool
    {
        if ($taxOrFee === null) {
            return false;
        }

        if ($taxOrFee->getAccountId() === $authAccountId) {
            return true;
        }

        return false;
    }

    private function validateUserStatus(UserDomainObject $authUser): void
    {
        if ($authUser->getCurrentAccountUser()?->getStatus() !== UserStatus::ACTIVE->name) {
            // Log the user out if their account is not active. This can happen if a user is
            // deactivated while they are logged in.
            $this->auth->logout();
            throw new UnauthorizedException(__('Your account is not active.'));
        }
    }
}
