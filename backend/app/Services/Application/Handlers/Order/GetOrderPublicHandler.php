<?php

namespace Evently\Services\Application\Handlers\Order;

use Evently\DomainObjects\AttendeeDomainObject;
use Evently\DomainObjects\EventDomainObject;
use Evently\DomainObjects\EventSettingDomainObject;
use Evently\DomainObjects\Generated\EventDomainObjectAbstract;
use Evently\DomainObjects\Generated\OrganizerDomainObjectAbstract;
use Evently\DomainObjects\Generated\ProductDomainObjectAbstract;
use Evently\DomainObjects\ImageDomainObject;
use Evently\DomainObjects\InvoiceDomainObject;
use Evently\DomainObjects\OrderDomainObject;
use Evently\DomainObjects\OrderItemDomainObject;
use Evently\DomainObjects\OrganizerDomainObject;
use Evently\DomainObjects\ProductDomainObject;
use Evently\DomainObjects\ProductPriceDomainObject;
use Evently\DomainObjects\Status\OrderStatus;
use Evently\Exceptions\UnauthorizedException;
use Evently\Repository\Eloquent\Value\Relationship;
use Evently\Repository\Interfaces\OrderRepositoryInterface;
use Evently\Services\Application\Handlers\Order\DTO\GetOrderPublicDTO;
use Evently\Services\Infrastructure\Session\CheckoutSessionManagementService;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class GetOrderPublicHandler
{
    public function __construct(
        private readonly OrderRepositoryInterface         $orderRepository,
        private readonly CheckoutSessionManagementService $sessionIdentifierService
    )
    {
    }

    public function handle(GetOrderPublicDTO $getOrderData): OrderDomainObject
    {
        $order = $this->getOrderDomainObject($getOrderData);

        if (!$order) {
            throw new ResourceNotFoundException(__('Order not found'));
        }

        if ($order->getStatus() === OrderStatus::RESERVED->name) {
            $this->verifySessionId($order->getSessionId());
        }

        return $order;
    }

    private function verifySessionId(string $orderSessionId): void
    {
        if (!$this->sessionIdentifierService->verifySession($orderSessionId)) {
            throw new UnauthorizedException(
                __('Sorry, we could not verify your session. Please restart your order.')
            );
        }
    }

    private function getOrderDomainObject(GetOrderPublicDTO $getOrderData): ?OrderDomainObject
    {
        $orderQuery = $this->orderRepository
            ->loadRelation(new Relationship(
                domainObject: AttendeeDomainObject::class,
                nested: [
                    new Relationship(
                        domainObject: ProductDomainObject::class,
                        nested: [
                            new Relationship(
                                domainObject: ProductPriceDomainObject::class,
                            )
                        ],
                        name: ProductDomainObjectAbstract::SINGULAR_NAME,
                    )
                ],
            ))
            ->loadRelation(new Relationship(domainObject: InvoiceDomainObject::class))
            ->loadRelation(new Relationship(
                domainObject: OrderItemDomainObject::class,
            ));

        if ($getOrderData->includeEventInResponse) {
            $orderQuery->loadRelation(new Relationship(
                domainObject: EventDomainObject::class,
                nested: [
                    new Relationship(
                        domainObject: EventSettingDomainObject::class,
                    ),
                    new Relationship(
                        domainObject: OrganizerDomainObject::class,
                        name: OrganizerDomainObjectAbstract::SINGULAR_NAME,
                    ),
                    new Relationship(
                        domainObject: ImageDomainObject::class,
                    )
                ],
                name: EventDomainObjectAbstract::SINGULAR_NAME
            ));
        }

        return $orderQuery->findByShortId($getOrderData->orderShortId);
    }
}
