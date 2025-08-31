<?php

namespace Evently\Http\Actions\TaxesAndFees;

use Evently\DomainObjects\AccountDomainObject;
use Evently\Exceptions\ResourceNameAlreadyExistsException;
use Evently\Http\Actions\BaseAction;
use Evently\Http\Request\TaxOrFee\CreateTaxOrFeeRequest;
use Evently\Resources\Tax\TaxAndFeeResource;
use Evently\Services\Application\Handlers\TaxAndFee\CreateTaxOrFeeHandler;
use Evently\Services\Application\Handlers\TaxAndFee\DTO\UpsertTaxDTO;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class CreateTaxOrFeeAction extends BaseAction
{
    private CreateTaxOrFeeHandler $taxHandler;

    public function __construct(CreateTaxOrFeeHandler $taxHandler)
    {
        $this->taxHandler = $taxHandler;
    }

    /**
     * @throws ValidationException
     */
    public function __invoke(CreateTaxOrFeeRequest $request, int $accountId): JsonResponse
    {
        $this->isActionAuthorized($accountId, AccountDomainObject::class);

        try {
            $payload = array_merge($request->validated(), [
                'account_id' => $this->getAuthenticatedAccountId(),
            ]);

            $tax = $this->taxHandler->handle(UpsertTaxDTO::fromArray($payload));
        } catch (ResourceNameAlreadyExistsException $e) {
            throw ValidationException::withMessages([
                'name' => $e->getMessage(),
            ]);
        }

        return $this->resourceResponse(TaxAndFeeResource::class, $tax);
    }
}
