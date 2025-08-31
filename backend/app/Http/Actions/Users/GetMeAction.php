<?php

namespace Evently\Http\Actions\Users;

use Evently\Http\Actions\Auth\BaseAuthAction;
use Evently\Resources\User\UserResource;
use Illuminate\Http\JsonResponse;

class GetMeAction extends BaseAuthAction
{
    public function __invoke(): JsonResponse
    {
        return $this->resourceResponse(
            resource: UserResource::class,
            data: $this->getAuthenticatedUser(),
        );
    }
}
