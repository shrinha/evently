<?php

namespace Evently\Services\Application\Handlers\Attendee;

use Brick\Money\Money;
use Evently\DomainObjects\AttendeeDomainObject;
use Evently\DomainObjects\Enums\ProductType;
use Evently\DomainObjects\Generated\AttendeeDomainObjectAbstract;
use Evently\DomainObjects\Generated\OrderDomainObjectAbstract;
use Evently\DomainObjects\Generated\OrderItemDomainObjectAbstract;
use Evently\DomainObjects\Generated\ProductDomainObjectAbstract;
use Evently\DomainObjects\OrderDomainObject;
use Evently\DomainObjects\OrderItemDomainObject;
use Evently\DomainObjects\ProductDomainObject;
use Evently\DomainObjects\ProductPriceDomainObject;
use Evently\DomainObjects\Status\AttendeeStatus;
use Evently\DomainObjects\Status\OrderPaymentStatus;
use Evently\DomainObjects\Status\OrderStatus;
use Evently\Events\OrderStatusChangedEvent;
use Evently\Exceptions\InvalidProductPriceId;
use Evently\Exceptions\NoTicketsAvailableException;
use Evently\Helper\IdHelper;
use Evently\Repository\Interfaces\AttendeeRepositoryInterface;
use Evently\Repository\Interfaces\EventRepositoryInterface;
use Evently\Repository\Interfaces\OrderRepositoryInterface;
use Evently\Repository\Interfaces\ProductRepositoryInterface;
use Evently\Repository\Interfaces\TaxAndFeeRepositoryInterface;
use Evently\Services\Application\Handlers\Attendee\DTO\CreateAttendeeDTO;
use Evently\Services\Application\Handlers\Attendee\DTO\CreateAttendeeTaxAndFeeDTO;
use Evently\Services\Domain\Order\OrderManagementService;
use Evently\Services\Domain\Product\ProductQuantityUpdateService;
use Evently\Services\Domain\Tax\TaxAndFeeRollupService;
use Evently\Services\Infrastructure\DomainEvents\DomainEventDispatcherService;
use Evently\Services\Infrastructure\DomainEvents\Enums\DomainEventType;
use Evently\Services\Infrastructure\DomainEvents\Events\OrderEvent;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Collection;
use RuntimeException;
use Throwable;

class CreateAttendeeHandler
{
    public function __construct(
        private readonly AttendeeRepositoryInterface  $attendeeRepository,
        private readonly OrderRepositoryInterface     $orderRepository,
        private readonly ProductRepositoryInterface   $productRepository,
        private readonly EventRepositoryInterface     $eventRepository,
        private readonly ProductQuantityUpdateService $productQuantityAdjustmentService,
        private readonly DatabaseManager              $databaseManager,
        private readonly TaxAndFeeRepositoryInterface $taxAndFeeRepository,
        private readonly TaxAndFeeRollupService       $taxAndFeeRollupService,
        private readonly OrderManagementService       $orderManagementService,
        private readonly DomainEventDispatcherService $domainEventDispatcherService,
    )
    {
    }

    /**
     * @throws NoTicketsAvailableException
     * @throws Throwable
     */
    public function handle(CreateAttendeeDTO $attendeeDTO): AttendeeDomainObject
    {
        return $this->databaseManager->transaction(function () use ($attendeeDTO) {
            $this->calculateTaxesAndFees($attendeeDTO);

            $order = $this->createOrder($attendeeDTO->event_id, $attendeeDTO);

            /** @var ProductDomainObject $product */
            $product = $this->productRepository
                ->loadRelation(ProductPriceDomainObject::class)
                ->findFirstWhere([
                    ProductDomainObjectAbstract::ID => $attendeeDTO->product_id,
                    ProductDomainObjectAbstract::EVENT_ID => $attendeeDTO->event_id,
                    ProductDomainObjectAbstract::PRODUCT_TYPE => ProductType::TICKET->name,
                ]);

            if (!$product) {
                throw new NoTicketsAvailableException(__('This ticket is invalid'));
            }

            $productPriceId = $this->getProductPriceId($attendeeDTO, $product);

            $availableQuantity = $this->productRepository->getQuantityRemainingForProductPrice(
                $attendeeDTO->product_id,
                $productPriceId,
            );

            if ($availableQuantity <= 0) {
                throw new NoTicketsAvailableException(__('There are no tickets available. ' .
                    'If you would like to assign a product to this attendee,' .
                    ' please adjust the product\'s available quantity.'));
            }

            $productPriceId = $this->getProductPriceId($attendeeDTO, $product);

            $this->processTaxesAndFees($attendeeDTO);

            $orderItem = $this->createOrderItem($attendeeDTO, $order, $product, $productPriceId);

            $attendee = $this->createAttendee($order, $attendeeDTO);

            $this->orderManagementService->updateOrderTotals($order, collect([$orderItem]));

            $this->fireEventsAndUpdateQuantities($attendeeDTO, $order);

            $this->queueWebhooks($order);

            return $attendee;
        });
    }

    private function createOrder(int $eventId, CreateAttendeeDTO $attendeeDTO): OrderDomainObject
    {
        $event = $this->eventRepository->findById($eventId);
        $total = Money::of($attendeeDTO->amount_paid, $event->getCurrency());

        return $this->orderRepository->create(
            [
                OrderDomainObjectAbstract::TOTAL_GROSS => $total->getAmount()->toFloat(),
                OrderDomainObjectAbstract::FIRST_NAME => $attendeeDTO->first_name,
                OrderDomainObjectAbstract::LAST_NAME => $attendeeDTO->last_name,
                OrderDomainObjectAbstract::EMAIL => $attendeeDTO->email,
                OrderDomainObjectAbstract::EVENT_ID => $eventId,
                OrderDomainObjectAbstract::SHORT_ID => IdHelper::shortId(IdHelper::ORDER_PREFIX),
                OrderDomainObjectAbstract::STATUS => OrderStatus::COMPLETED->name,
                OrderDomainObjectAbstract::PAYMENT_STATUS => $total->isZero()
                    ? OrderPaymentStatus::NO_PAYMENT_REQUIRED->name
                    : OrderPaymentStatus::PAYMENT_RECEIVED->name,
                OrderDomainObjectAbstract::CURRENCY => $event->getCurrency(),
                OrderDomainObjectAbstract::PUBLIC_ID => IdHelper::publicId(IdHelper::ORDER_PREFIX),
                OrderDomainObjectAbstract::IS_MANUALLY_CREATED => true,
                OrderDomainObjectAbstract::LOCALE => $attendeeDTO->locale,
            ]
        );
    }

    /**
     * @throws InvalidProductPriceId
     */
    private function getProductPriceId(CreateAttendeeDTO $attendeeDTO, ProductDomainObject $product): int
    {
        $priceIds = $product->getProductPrices()->map(fn(ProductPriceDomainObject $productPrice) => $productPrice->getId());

        if ($attendeeDTO->product_price_id) {
            if (!$priceIds->contains($attendeeDTO->product_price_id)) {
                throw new InvalidProductPriceId(__('The product price ID is invalid.'));
            }
            return $attendeeDTO->product_price_id;
        }

        /** @var ProductPriceDomainObject $productPrice */
        $productPrice = $product->getProductPrices()->first();

        if ($productPrice) {
            return $productPrice->getId();
        }

        throw new InvalidProductPriceId(__('The product price ID is invalid.'));
    }

    private function calculateTaxesAndFees(CreateAttendeeDTO $attendeeDTO): ?Collection
    {
        if (!$attendeeDTO->taxes_and_fees) {
            return null;
        }

        $taxesAndFees = $this->taxAndFeeRepository->findWhereIn(
            'id',
            $attendeeDTO
                ->taxes_and_fees
                ->map(fn(CreateAttendeeTaxAndFeeDTO $taxAndFee) => $taxAndFee->tax_or_fee_id)
                ->toArray()
        );

        $validatedTaxesAndFees = collect();
        $attendeeDTO->taxes_and_fees->each(function (CreateAttendeeTaxAndFeeDTO $taxAndFee) use ($validatedTaxesAndFees, $taxesAndFees) {
            $taxOrFee = $taxesAndFees->first(fn($taxOrFee) => $taxOrFee->getId() === $taxAndFee->tax_or_fee_id);

            if (!$taxOrFee) {
                throw new RuntimeException('Tax or fee not found.');
            }

            $validatedTaxesAndFees->push($taxOrFee);
        });

        return $validatedTaxesAndFees;
    }

    private function processTaxesAndFees(CreateAttendeeDTO $attendeeDTO): void
    {
        $this->calculateTaxesAndFees($attendeeDTO)
            ?->each(fn($taxOrFee) => $this->taxAndFeeRollupService
                ->addToRollUp(
                    $taxOrFee,
                    $attendeeDTO
                        ->taxes_and_fees
                        ->first(fn($taxOrFeeDTO) => $taxOrFeeDTO->tax_or_fee_id === $taxOrFee->getId())
                        ->amount)
            );
    }

    private function createOrderItem(CreateAttendeeDTO $attendeeDTO, OrderDomainObject $order, ProductDomainObject $product, int $productPriceId): OrderItemDomainObject
    {
        return $this->orderRepository->addOrderItem(
            [
                OrderItemDomainObjectAbstract::PRODUCT_ID => $attendeeDTO->product_id,
                OrderItemDomainObjectAbstract::QUANTITY => 1,
                OrderItemDomainObjectAbstract::TOTAL_BEFORE_ADDITIONS => $attendeeDTO->amount_paid,
                OrderItemDomainObjectAbstract::TOTAL_GROSS => $attendeeDTO->amount_paid + $this->taxAndFeeRollupService->getTotalTaxesAndFees(),
                OrderItemDomainObjectAbstract::TOTAL_TAX => $this->taxAndFeeRollupService->getTotalTaxes(),
                OrderItemDomainObjectAbstract::TOTAL_SERVICE_FEE => $this->taxAndFeeRollupService->getTotalFees(),
                OrderItemDomainObjectAbstract::PRICE => $attendeeDTO->amount_paid,
                OrderItemDomainObjectAbstract::ORDER_ID => $order->getId(),
                OrderItemDomainObjectAbstract::ITEM_NAME => $product->getTitle(),
                OrderItemDomainObjectAbstract::PRODUCT_PRICE_ID => $productPriceId,
                OrderItemDomainObjectAbstract::TAXES_AND_FEES_ROLLUP => $this->taxAndFeeRollupService->getRollUp(),
            ]
        );
    }

    private function createAttendee(OrderDomainObject $order, CreateAttendeeDTO $attendeeDTO): AttendeeDomainObject
    {
        return $this->attendeeRepository->create([
            AttendeeDomainObjectAbstract::EVENT_ID => $order->getEventId(),
            AttendeeDomainObjectAbstract::PRODUCT_ID => $attendeeDTO->product_id,
            AttendeeDomainObjectAbstract::PRODUCT_PRICE_ID => $attendeeDTO->product_price_id,
            AttendeeDomainObjectAbstract::STATUS => AttendeeStatus::ACTIVE->name,
            AttendeeDomainObjectAbstract::EMAIL => $attendeeDTO->email,
            AttendeeDomainObjectAbstract::FIRST_NAME => $attendeeDTO->first_name,
            AttendeeDomainObjectAbstract::LAST_NAME => $attendeeDTO->last_name,
            AttendeeDomainObjectAbstract::ORDER_ID => $order->getId(),
            AttendeeDomainObjectAbstract::PUBLIC_ID => IdHelper::publicId(IdHelper::ATTENDEE_PREFIX),
            AttendeeDomainObjectAbstract::SHORT_ID => IdHelper::shortId(IdHelper::ATTENDEE_PREFIX),
            AttendeeDomainObjectAbstract::LOCALE => $attendeeDTO->locale,
        ]);
    }

    private function fireEventsAndUpdateQuantities(CreateAttendeeDTO $attendeeDTO, OrderDomainObject $order): void
    {
        $this->productQuantityAdjustmentService->increaseQuantitySold(
            priceId: $attendeeDTO->product_price_id,
        );

        event(new OrderStatusChangedEvent(
            order: $order,
            sendEmails: $attendeeDTO->send_confirmation_email,
        ));
    }

    private function queueWebhooks(OrderDomainObject $order): void
    {
        $this->domainEventDispatcherService->dispatch(
            new OrderEvent(DomainEventType::ORDER_CREATED, $order->getId())
        );
    }
}
