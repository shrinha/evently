<?php

namespace Evently\Services\Domain\Order;

use Evently\DomainObjects\EventDomainObject;
use Evently\DomainObjects\Generated\ProductDomainObjectAbstract;
use Evently\DomainObjects\OrderDomainObject;
use Evently\DomainObjects\ProductDomainObject;
use Evently\DomainObjects\ProductPriceDomainObject;
use Evently\DomainObjects\PromoCodeDomainObject;
use Evently\DomainObjects\TaxAndFeesDomainObject;
use Evently\Helper\Currency;
use Evently\Repository\Interfaces\OrderRepositoryInterface;
use Evently\Repository\Interfaces\ProductRepositoryInterface;
use Evently\Services\Application\Handlers\Order\DTO\ProductOrderDetailsDTO;
use Evently\Services\Domain\Product\DTO\OrderProductPriceDTO;
use Evently\Services\Domain\Product\ProductPriceService;
use Evently\Services\Domain\Tax\TaxAndFeeCalculationService;
use Illuminate\Support\Collection;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

readonly class OrderItemProcessingService
{
    public function __construct(
        private OrderRepositoryInterface    $orderRepository,
        private ProductRepositoryInterface  $productRepository,
        private TaxAndFeeCalculationService $taxCalculationService,
        private ProductPriceService         $productPriceService,
    )
    {
    }

    /**
     * @param OrderDomainObject $order
     * @param Collection<ProductOrderDetailsDTO> $productsOrderDetails
     * @param EventDomainObject $event
     * @param PromoCodeDomainObject|null $promoCode
     * @return Collection
     */
    public function process(
        OrderDomainObject      $order,
        Collection             $productsOrderDetails,
        EventDomainObject      $event,
        ?PromoCodeDomainObject $promoCode
    ): Collection
    {
        $orderItems = collect();

        foreach ($productsOrderDetails as $productOrderDetail) {
            $product = $this->productRepository
                ->loadRelation(TaxAndFeesDomainObject::class)
                ->loadRelation(ProductPriceDomainObject::class)
                ->findFirstWhere([
                    ProductDomainObjectAbstract::ID => $productOrderDetail->product_id,
                    ProductDomainObjectAbstract::EVENT_ID => $event->getId(),
                ]);

            if ($product === null) {
                throw new ResourceNotFoundException(
                    __('Product with id :id not found', ['id' => $productOrderDetail->product_id])
                );
            }

            $productOrderDetail->quantities->each(function (OrderProductPriceDTO $productPrice) use ($promoCode, $order, $orderItems, $product) {
                if ($productPrice->quantity === 0) {
                    return;
                }
                $orderItemData = $this->calculateOrderItemData($product, $productPrice, $order, $promoCode);
                $orderItems->push($this->orderRepository->addOrderItem($orderItemData));
            });
        }

        return $orderItems;
    }

    private function calculateOrderItemData(
        ProductDomainObject    $product,
        OrderProductPriceDTO   $productPriceDetails,
        OrderDomainObject      $order,
        ?PromoCodeDomainObject $promoCode
    ): array
    {
        $prices = $this->productPriceService->getPrice($product, $productPriceDetails, $promoCode);
        $priceWithDiscount = $prices->price;
        $priceBeforeDiscount = $prices->price_before_discount;

        $itemTotalWithDiscount = $priceWithDiscount * $productPriceDetails->quantity;

        $taxesAndFees = $this->taxCalculationService->calculateTaxAndFeesForProduct(
            product: $product,
            price: $priceWithDiscount,
            quantity: $productPriceDetails->quantity
        );

        return [
            'product_type' => $product->getProductType(),
            'product_id' => $product->getId(),
            'product_price_id' => $productPriceDetails->price_id,
            'quantity' => $productPriceDetails->quantity,
            'price_before_discount' => $priceBeforeDiscount,
            'total_before_additions' => Currency::round($itemTotalWithDiscount),
            'price' => $priceWithDiscount,
            'order_id' => $order->getId(),
            'item_name' => $this->getOrderItemLabel($product, $productPriceDetails->price_id),
            'total_tax' => $taxesAndFees->taxTotal,
            'total_service_fee' => $taxesAndFees->feeTotal,
            'total_gross' => Currency::round($itemTotalWithDiscount + $taxesAndFees->taxTotal + $taxesAndFees->feeTotal),
            'taxes_and_fees_rollup' => $taxesAndFees->rollUp,
        ];
    }

    private function getOrderItemLabel(ProductDomainObject $product, int $priceId): string
    {
        if ($product->isTieredType()) {
            return $product->getTitle() . ' - ' . $product->getProductPrices()
                    ?->filter(fn($p) => $p->getId() === $priceId)->first()
                    ?->getLabel();
        }

        return $product->getTitle();
    }
}
