<?php

namespace Tests\Unit\Services\Domain\Order;

use Evently\DomainObjects\AttendeeDomainObject;
use Evently\DomainObjects\EventDomainObject;
use Evently\DomainObjects\EventSettingDomainObject;
use Evently\DomainObjects\OrderDomainObject;
use Evently\DomainObjects\OrganizerDomainObject;
use Evently\DomainObjects\Status\AttendeeStatus;
use Evently\Mail\Order\OrderCancelled;
use Evently\Repository\Interfaces\AttendeeRepositoryInterface;
use Evently\Repository\Interfaces\EventRepositoryInterface;
use Evently\Repository\Interfaces\OrderRepositoryInterface;
use Evently\Services\Domain\Order\OrderCancelService;
use Evently\Services\Domain\Product\ProductQuantityUpdateService;
use Evently\Services\Infrastructure\DomainEvents\DomainEventDispatcherService;
use Evently\Services\Infrastructure\DomainEvents\Enums\DomainEventType;
use Evently\Services\Infrastructure\DomainEvents\Events\OrderEvent;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Collection;
use Mockery as m;
use Tests\TestCase;
use Throwable;

class OrderCancelServiceTest extends TestCase
{
    private Mailer $mailer;
    private AttendeeRepositoryInterface $attendeeRepository;
    private EventRepositoryInterface $eventRepository;
    private OrderRepositoryInterface $orderRepository;
    private DatabaseManager $databaseManager;
    private ProductQuantityUpdateService $productQuantityService;
    private OrderCancelService $service;
    private DomainEventDispatcherService $domainEventDispatcherService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mailer = m::mock(Mailer::class);
        $this->attendeeRepository = m::mock(AttendeeRepositoryInterface::class);
        $this->eventRepository = m::mock(EventRepositoryInterface::class);
        $this->orderRepository = m::mock(OrderRepositoryInterface::class);
        $this->databaseManager = m::mock(DatabaseManager::class);
        $this->productQuantityService = m::mock(ProductQuantityUpdateService::class);
        $this->domainEventDispatcherService = m::mock(DomainEventDispatcherService::class);

        $this->service = new OrderCancelService(
            mailer: $this->mailer,
            attendeeRepository: $this->attendeeRepository,
            eventRepository: $this->eventRepository,
            orderRepository: $this->orderRepository,
            databaseManager: $this->databaseManager,
            productQuantityService: $this->productQuantityService,
            domainEventDispatcherService: $this->domainEventDispatcherService,
        );
    }

    public function testCancelOrder(): void
    {
        $order = m::mock(OrderDomainObject::class);
        $order->shouldReceive('getEventId')->andReturn(1);
        $order->shouldReceive('getId')->andReturn(1);
        $order->shouldReceive('getEmail')->andReturn('customer@example.com');
        $order->shouldReceive('isOrderAwaitingOfflinePayment')->andReturn(false);

        $order->shouldReceive('getLocale')->andReturn('en');

        $attendees = new Collection([
            m::mock(AttendeeDomainObject::class)->shouldReceive('getproductPriceId')->andReturn(1)->mock(),
            m::mock(AttendeeDomainObject::class)->shouldReceive('getproductPriceId')->andReturn(2)->mock(),
        ]);

        $this->attendeeRepository
            ->shouldReceive('findWhere')
            ->once()
            ->with([
                'order_id' => $order->getId(),
            ])
            ->andReturn($attendees);

        $this->attendeeRepository->shouldReceive('updateWhere')->once();

        $this->productQuantityService->shouldReceive('decreaseQuantitySold')->twice();

        $this->orderRepository->shouldReceive('updateWhere')->once();

        $event = new EventDomainObject();
        $event->setEventSettings(new EventSettingDomainObject());
        $event->setOrganizer(new OrganizerDomainObject());
        $this->eventRepository
            ->shouldReceive('loadRelation')
            ->twice()
            ->andReturnSelf()
            ->getMock()
            ->shouldReceive('findById')->once()->andReturn($event);

        $this->mailer->shouldReceive('to')
            ->once()
            ->andReturnSelf();

        $this->mailer->shouldReceive('locale')
            ->once()
            ->andReturnSelf();

        $this->mailer->shouldReceive('send')->once()->withArgs(function ($mail) {
            return $mail instanceof OrderCancelled;
        });

        $this->domainEventDispatcherService->shouldReceive('dispatch')
            ->withArgs(function (OrderEvent $event) use ($order) {
                return $event->type === DomainEventType::ORDER_CANCELLED
                    && $event->orderId === $order->getId();
            })
            ->once();

        $this->databaseManager->shouldReceive('transaction')->once()->andReturnUsing(function ($callback) {
            $callback();
        });

        $attendees->each(function ($attendee) {
            $attendee->shouldReceive('getStatus')->andReturn(AttendeeStatus::ACTIVE->name);
        });

        try {
            $this->service->cancelOrder($order);
        } catch (Throwable $e) {
            $this->fail("Failed to cancel order: " . $e->getMessage());
        }

        $this->assertTrue(true, "Order cancellation proceeded without throwing an exception.");
    }

    public function testCancelOrderAwaitingOfflinePayment(): void
    {
        $order = m::mock(OrderDomainObject::class);
        $order->shouldReceive('getEventId')->andReturn(1);
        $order->shouldReceive('getId')->andReturn(1);
        $order->shouldReceive('getEmail')->andReturn('customer@example.com');
        $order->shouldReceive('isOrderAwaitingOfflinePayment')->andReturn(true);
        $order->shouldReceive('getLocale')->andReturn('en');

        $attendees = new Collection([
            m::mock(AttendeeDomainObject::class)->shouldReceive('getproductPriceId')->andReturn(1)->mock(),
            m::mock(AttendeeDomainObject::class)->shouldReceive('getproductPriceId')->andReturn(2)->mock(),
        ]);

        $this->attendeeRepository
            ->shouldReceive('findWhere')
            ->once()
            ->with([
                'order_id' => $order->getId(),
            ])
            ->andReturn($attendees);

        $this->attendeeRepository->shouldReceive('updateWhere')->once();

        $this->productQuantityService->shouldReceive('decreaseQuantitySold')->twice();

        $this->orderRepository->shouldReceive('updateWhere')->once();

        $event = new EventDomainObject();
        $event->setEventSettings(new EventSettingDomainObject());
        $event->setOrganizer(new OrganizerDomainObject());
        $this->eventRepository
            ->shouldReceive('loadRelation')
            ->twice()
            ->andReturnSelf()
            ->getMock()
            ->shouldReceive('findById')->once()->andReturn($event);

        $this->mailer->shouldReceive('to')
            ->once()
            ->andReturnSelf();

        $this->mailer->shouldReceive('locale')
            ->once()
            ->andReturnSelf();

        $this->mailer->shouldReceive('send')->once()->withArgs(function ($mail) {
            return $mail instanceof OrderCancelled;
        });

        $this->domainEventDispatcherService->shouldReceive('dispatch')
            ->withArgs(function (OrderEvent $event) use ($order) {
                return $event->type === DomainEventType::ORDER_CANCELLED
                    && $event->orderId === $order->getId();
            })
            ->once();

        $this->databaseManager->shouldReceive('transaction')->once()->andReturnUsing(function ($callback) {
            $callback();
        });

        $attendees->each(function ($attendee) {
            $attendee->shouldReceive('getStatus')->andReturn(AttendeeStatus::AWAITING_PAYMENT->name);
        });

        try {
            $this->service->cancelOrder($order);
        } catch (Throwable $e) {
            $this->fail("Failed to cancel order: " . $e->getMessage());
        }

        $this->assertTrue(true, "Order cancellation proceeded without throwing an exception.");
    }
}
