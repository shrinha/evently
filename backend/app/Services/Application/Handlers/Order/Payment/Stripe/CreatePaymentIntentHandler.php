<?php

namespace Evently\Services\Application\Handlers\Order\Payment\Stripe;

use Brick\Math\Exception\MathException;
use Brick\Math\Exception\NumberFormatException;
use Brick\Math\Exception\RoundingNecessaryException;
use Brick\Money\Exception\UnknownCurrencyException;
use Evently\DomainObjects\AccountConfigurationDomainObject;
use Evently\DomainObjects\Generated\StripePaymentDomainObjectAbstract;
use Evently\DomainObjects\OrderItemDomainObject;
use Evently\DomainObjects\Status\OrderStatus;
use Evently\DomainObjects\StripePaymentDomainObject;
use Evently\Exceptions\ResourceConflictException;
use Evently\Exceptions\Stripe\CreatePaymentIntentFailedException;
use Evently\Exceptions\UnauthorizedException;
use Evently\Repository\Eloquent\Value\Relationship;
use Evently\Repository\Interfaces\AccountRepositoryInterface;
use Evently\Repository\Interfaces\OrderRepositoryInterface;
use Evently\Repository\Interfaces\StripePaymentsRepositoryInterface;
use Evently\Services\Domain\Payment\Stripe\DTOs\CreatePaymentIntentRequestDTO;
use Evently\Services\Domain\Payment\Stripe\DTOs\CreatePaymentIntentResponseDTO;
use Evently\Services\Domain\Payment\Stripe\StripePaymentIntentCreationService;
use Evently\Services\Infrastructure\Session\CheckoutSessionManagementService;
use Evently\Values\MoneyValue;
use Stripe\Exception\ApiErrorException;
use Throwable;

readonly class CreatePaymentIntentHandler
{
    public function __construct(
        private OrderRepositoryInterface           $orderRepository,
        private StripePaymentIntentCreationService $stripePaymentService,
        private CheckoutSessionManagementService   $sessionIdentifierService,
        private StripePaymentsRepositoryInterface  $stripePaymentsRepository,
        private AccountRepositoryInterface         $accountRepository,
    )
    {
    }

    /**
     * @param string $orderShortId
     * @return CreatePaymentIntentResponseDTO
     * @throws CreatePaymentIntentFailedException
     * @throws MathException
     * @throws NumberFormatException
     * @throws RoundingNecessaryException
     * @throws UnknownCurrencyException
     * @throws ApiErrorException
     * @throws Throwable
     */
    public function handle(string $orderShortId): CreatePaymentIntentResponseDTO
    {
        $order = $this->orderRepository
            ->loadRelation(new Relationship(OrderItemDomainObject::class))
            ->loadRelation(new Relationship(StripePaymentDomainObject::class, name: 'stripe_payment'))
            ->findByShortId($orderShortId);

        if (!$order || !$this->sessionIdentifierService->verifySession($order->getSessionId())) {
            throw new UnauthorizedException(__('Sorry, we could not verify your session. Please create a new order.'));
        }

        if ($order->getStatus() !== OrderStatus::RESERVED->name || $order->isReservedOrderExpired()) {
            throw new ResourceConflictException(__('Sorry, is expired or not in a valid state.'));
        }

        $account = $this->accountRepository
            ->loadRelation(new Relationship(
                domainObject: AccountConfigurationDomainObject::class,
                name: 'configuration',
            ))
            ->findByEventId($order->getEventId());

        // If we already have a Stripe session then re-fetch the client secret
        if ($order->getStripePayment() !== null) {
            return new CreatePaymentIntentResponseDTO(
                paymentIntentId: $order->getStripePayment()->getPaymentIntentId(),
                clientSecret: $this->stripePaymentService->retrievePaymentIntentClientSecret(
                    $order->getStripePayment()->getPaymentIntentId(),
                    $account->getStripeAccountId()
                ),
                accountId: $account->getStripeAccountId(),
            );
        }

        $paymentIntent = $this->stripePaymentService->createPaymentIntent(CreatePaymentIntentRequestDTO::fromArray([
            'amount' => MoneyValue::fromFloat($order->getTotalGross(), $order->getCurrency()),
            'currencyCode' => $order->getCurrency(),
            'account' => $account,
            'order' => $order,
        ]));

        $this->stripePaymentsRepository->create([
            StripePaymentDomainObjectAbstract::ORDER_ID => $order->getId(),
            StripePaymentDomainObjectAbstract::PAYMENT_INTENT_ID => $paymentIntent->paymentIntentId,
            StripePaymentDomainObjectAbstract::CONNECTED_ACCOUNT_ID => $account->getStripeAccountId(),
            StripePaymentDomainObjectAbstract::APPLICATION_FEE => $paymentIntent->applicationFeeAmount,
        ]);

        return $paymentIntent;
    }
}
