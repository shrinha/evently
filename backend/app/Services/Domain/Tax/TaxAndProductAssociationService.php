<?php

namespace Evently\Services\Domain\Tax;

use Exception;
use Evently\Exceptions\InvalidTaxOrFeeIdException;
use Evently\Repository\Interfaces\ProductRepositoryInterface;
use Evently\Repository\Interfaces\TaxAndFeeRepositoryInterface;
use Evently\Services\Domain\Tax\DTO\TaxAndProductAssociateParams;
use Illuminate\Support\Collection;

readonly class TaxAndProductAssociationService
{
    public function __construct(
        private TaxAndFeeRepositoryInterface $taxAndFeeRepository,
        private ProductRepositoryInterface   $ticketRepository,
    )
    {
    }

    /**
     * @throws Exception
     */
    public function addTaxesToProduct(TaxAndProductAssociateParams $params): Collection
    {
        $taxesAndFees = $this->taxAndFeeRepository->findWhereIn(
            field: 'id',
            values: $params->taxAndFeeIds,
            additionalWhere: [
                'account_id' => $params->accountId,
                'is_active' => true,
            ],
        );

        if (count($params->taxAndFeeIds) !== $taxesAndFees->count()) {
            throw new InvalidTaxOrFeeIdException(__('One or more tax IDs are invalid'));
        }

        $this->ticketRepository->addTaxesAndFeesToProduct($params->productId, $params->taxAndFeeIds);

        return $taxesAndFees;
    }
}
