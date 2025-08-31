<?php

namespace Evently\Http\Actions\TaxesAndFees;

use Evently\DomainObjects\TaxAndFeesDomainObject;
use Evently\Exceptions\ResourceNameAlreadyExistsException;
use Evently\Http\Actions\BaseAction;
use Evently\Http\Request\TaxOrFee\CreateTaxOrFeeRequest;
use Evently\Resources\Tax\TaxAndFeeResource;
use Evently\Services\Application\Handlers\TaxAndFee\DTO\UpsertTaxDTO;
use Evently\Services\Application\Handlers\TaxAndFee\EditTaxHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class EditTaxOrFeeAction extends BaseAction
{
    private EditTaxHandler $taxHandler;

    public function __construct(EditTaxHandler $taxHandler)
    {
        $this->taxHandler = $taxHandler;
    }

    /**
     * @throws ValidationException
     */
    public function __invoke(CreateTaxOrFeeRequest $request, int $accountId, int $taxId): JsonResponse
    {
        $this->isActionAuthorized($taxId, TaxAndFeesDomainObject::class);

        try {
            $payload = array_merge($request->validated(), [
                'account_id' => $this->getAuthenticatedAccountId(),
                'id' => $taxId,
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
