<?php

declare(strict_types=1);

namespace Evently\Http\Actions\Auth;

use Evently\Exceptions\UnauthorizedException;
use Evently\Http\Request\Auth\LoginRequest;
use Evently\Http\ResponseCodes;
use Evently\Services\Application\Handlers\Auth\DTO\LoginCredentialsDTO;
use Evently\Services\Application\Handlers\Auth\LoginHandler;
use Illuminate\Http\JsonResponse;

class LoginAction extends BaseAuthAction
{
    private LoginHandler $loginHandler;

    public function __construct(LoginHandler $loginHandler)
    {
        $this->loginHandler = $loginHandler;
    }

    public function __invoke(LoginRequest $request): JsonResponse
    {
        try {
            $loginResponse = $this->loginHandler->handle(new LoginCredentialsDTO(
                email: strtolower($request->validated('email')),
                password: $request->validated('password'),
                accountId: (int)$request->validated('account_id'),
            ));
        } catch (UnauthorizedException $e) {
            return $this->errorResponse(
                message: $e->getMessage(),
                statusCode: ResponseCodes::HTTP_UNAUTHORIZED,
            );
        }

        return $this->respondWithToken($loginResponse->token, $loginResponse->accounts);
    }
}
