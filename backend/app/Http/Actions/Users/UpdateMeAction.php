<?php

namespace Evently\Http\Actions\Users;

use Evently\Exceptions\PasswordInvalidException;
use Evently\Http\Actions\BaseAction;
use Evently\Http\Request\User\UpdateMeRequest;
use Evently\Resources\User\UserResource;
use Evently\Services\Application\Handlers\User\DTO\UpdateMeDTO;
use Evently\Services\Application\Handlers\User\UpdateMeHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class UpdateMeAction extends BaseAction
{
    private UpdateMeHandler $updateUserHandler;

    public function __construct(UpdateMeHandler $updateUserHandler)
    {
        $this->updateUserHandler = $updateUserHandler;
    }

    /**
     * @throws ValidationException
     */
    public function __invoke(UpdateMeRequest $request): JsonResponse
    {
        try {
            $user = $this->updateUserHandler->handle(UpdateMeDTO::fromArray([
                'id' => $this->getAuthenticatedUser()->getId(),
                'account_id' => $this->getAuthenticatedAccountId(),
                'first_name' => $request->validated('first_name'),
                'last_name' => $request->validated('last_name'),
                'email' => $request->validated('email'),
                'password' => $request->validated('password'),
                'current_password' => $request->validated('current_password'),
                'timezone' => $request->validated('timezone'),
                'locale' => $request->validated('locale'),
            ]));

            return $this->resourceResponse(UserResource::class, $user);
        } catch (PasswordInvalidException) {
            throw ValidationException::withMessages([
                'current_password' => 'The current password does not match our records.',
            ]);
        }
    }
}
