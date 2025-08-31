<?php

namespace Evently\Resources\Auth;

use Evently\Resources\Account\AccountResource;
use Evently\Resources\User\UserResource;
use Evently\Services\Application\Handlers\Auth\DTO\AuthenticatedResponseDTO;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin AuthenticatedResponseDTO
 */
class AuthenticatedResponseResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'token' => $this->token,
            'token_type' => 'bearer',
            'expires_in' => $this->expiresIn,
            'user' => new UserResource($this->user),
            'accounts' => AccountResource::collection($this->accounts),
        ];
    }
}
