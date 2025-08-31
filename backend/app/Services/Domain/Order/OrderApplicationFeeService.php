<?php

namespace Evently\Services\Domain\Order;

use Evently\DomainObjects\Enums\PaymentProviders;
use Evently\DomainObjects\Generated\OrderApplicationFeeDomainObjectAbstract;
use Evently\DomainObjects\Status\OrderApplicationFeeStatus;
use Evently\Helper\Currency;
use Evently\Repository\Interfaces\OrderApplicationFeeRepositoryInterface;

class OrderApplicationFeeService
{
    public function __construct(
        private readonly OrderApplicationFeeRepositoryInterface $orderApplicationFeeRepository,
    )
    {
    }

    public function createOrderApplicationFee(
        int                       $orderId,
        int                       $applicationFeeAmountMinorUnit,
        OrderApplicationFeeStatus $orderApplicationFeeStatus,
        PaymentProviders          $paymentMethod,
        string                    $currency,
    ): void
    {
        $isZeroDecimalCurrency = Currency::isZeroDecimalCurrency($currency);

        $applicationFeeAmount = $isZeroDecimalCurrency
            ? $applicationFeeAmountMinorUnit
            : $applicationFeeAmountMinorUnit / 100;

        $this->orderApplicationFeeRepository->create([
            OrderApplicationFeeDomainObjectAbstract::ORDER_ID => $orderId,
            OrderApplicationFeeDomainObjectAbstract::AMOUNT => $applicationFeeAmount,
            OrderApplicationFeeDomainObjectAbstract::STATUS => $orderApplicationFeeStatus->value,
            OrderApplicationFeeDomainObjectAbstract::PAYMENT_METHOD => $paymentMethod->value,
            ORderApplicationFeeDomainObjectAbstract::CURRENCY => $currency,
            OrderApplicationFeeDomainObjectAbstract::PAID_AT => $orderApplicationFeeStatus->value === OrderApplicationFeeStatus::PAID->value
                ? now()->toDateTimeString()
                : null,
        ]);
    }
}
