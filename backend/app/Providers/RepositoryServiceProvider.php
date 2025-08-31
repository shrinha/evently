<?php

declare(strict_types=1);

namespace Evently\Providers;

use Evently\Repository\Eloquent\AccountConfigurationRepository;
use Evently\Repository\Eloquent\AccountRepository;
use Evently\Repository\Eloquent\AccountUserRepository;
use Evently\Repository\Eloquent\AffiliateRepository;
use Evently\Repository\Eloquent\AttendeeCheckInRepository;
use Evently\Repository\Eloquent\AttendeeRepository;
use Evently\Repository\Eloquent\CapacityAssignmentRepository;
use Evently\Repository\Eloquent\CheckInListRepository;
use Evently\Repository\Eloquent\EventDailyStatisticRepository;
use Evently\Repository\Eloquent\EventRepository;
use Evently\Repository\Eloquent\EventSettingsRepository;
use Evently\Repository\Eloquent\EventStatisticRepository;
use Evently\Repository\Eloquent\ImageRepository;
use Evently\Repository\Eloquent\InvoiceRepository;
use Evently\Repository\Eloquent\MessageRepository;
use Evently\Repository\Eloquent\OrderApplicationFeeRepository;
use Evently\Repository\Eloquent\OrderItemRepository;
use Evently\Repository\Eloquent\OrderRefundRepository;
use Evently\Repository\Eloquent\OrderRepository;
use Evently\Repository\Eloquent\OrganizerRepository;
use Evently\Repository\Eloquent\OrganizerSettingsRepository;
use Evently\Repository\Eloquent\OutgoingMessageRepository;
use Evently\Repository\Eloquent\PasswordResetRepository;
use Evently\Repository\Eloquent\PasswordResetTokenRepository;
use Evently\Repository\Eloquent\ProductCategoryRepository;
use Evently\Repository\Eloquent\ProductPriceRepository;
use Evently\Repository\Eloquent\ProductRepository;
use Evently\Repository\Eloquent\PromoCodeRepository;
use Evently\Repository\Eloquent\QuestionAndAnswerViewRepository;
use Evently\Repository\Eloquent\QuestionAnswerRepository;
use Evently\Repository\Eloquent\QuestionRepository;
use Evently\Repository\Eloquent\StripeCustomerRepository;
use Evently\Repository\Eloquent\StripePaymentsRepository;
use Evently\Repository\Eloquent\TaxAndFeeRepository;
use Evently\Repository\Eloquent\UserRepository;
use Evently\Repository\Eloquent\WebhookLogRepository;
use Evently\Repository\Eloquent\WebhookRepository;
use Evently\Repository\Interfaces\AccountConfigurationRepositoryInterface;
use Evently\Repository\Interfaces\AccountRepositoryInterface;
use Evently\Repository\Interfaces\AccountUserRepositoryInterface;
use Evently\Repository\Interfaces\AffiliateRepositoryInterface;
use Evently\Repository\Interfaces\AttendeeCheckInRepositoryInterface;
use Evently\Repository\Interfaces\AttendeeRepositoryInterface;
use Evently\Repository\Interfaces\CapacityAssignmentRepositoryInterface;
use Evently\Repository\Interfaces\CheckInListRepositoryInterface;
use Evently\Repository\Interfaces\EventDailyStatisticRepositoryInterface;
use Evently\Repository\Interfaces\EventRepositoryInterface;
use Evently\Repository\Interfaces\EventSettingsRepositoryInterface;
use Evently\Repository\Interfaces\EventStatisticRepositoryInterface;
use Evently\Repository\Interfaces\ImageRepositoryInterface;
use Evently\Repository\Interfaces\InvoiceRepositoryInterface;
use Evently\Repository\Interfaces\MessageRepositoryInterface;
use Evently\Repository\Interfaces\OrderApplicationFeeRepositoryInterface;
use Evently\Repository\Interfaces\OrderItemRepositoryInterface;
use Evently\Repository\Interfaces\OrderRefundRepositoryInterface;
use Evently\Repository\Interfaces\OrderRepositoryInterface;
use Evently\Repository\Interfaces\OrganizerRepositoryInterface;
use Evently\Repository\Interfaces\OrganizerSettingsRepositoryInterface;
use Evently\Repository\Interfaces\OutgoingMessageRepositoryInterface;
use Evently\Repository\Interfaces\PasswordResetRepositoryInterface;
use Evently\Repository\Interfaces\PasswordResetTokenRepositoryInterface;
use Evently\Repository\Interfaces\ProductCategoryRepositoryInterface;
use Evently\Repository\Interfaces\ProductPriceRepositoryInterface;
use Evently\Repository\Interfaces\ProductRepositoryInterface;
use Evently\Repository\Interfaces\PromoCodeRepositoryInterface;
use Evently\Repository\Interfaces\QuestionAndAnswerViewRepositoryInterface;
use Evently\Repository\Interfaces\QuestionAnswerRepositoryInterface;
use Evently\Repository\Interfaces\QuestionRepositoryInterface;
use Evently\Repository\Interfaces\StripeCustomerRepositoryInterface;
use Evently\Repository\Interfaces\StripePaymentsRepositoryInterface;
use Evently\Repository\Interfaces\TaxAndFeeRepositoryInterface;
use Evently\Repository\Interfaces\UserRepositoryInterface;
use Evently\Repository\Interfaces\WebhookLogRepositoryInterface;
use Evently\Repository\Interfaces\WebhookRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * @todo - find a way to auto-bind these
     */
    private static array $interfaceToConcreteMap = [
        UserRepositoryInterface::class => UserRepository::class,
        AccountRepositoryInterface::class => AccountRepository::class,
        EventRepositoryInterface::class => EventRepository::class,
        ProductRepositoryInterface::class => ProductRepository::class,
        OrderRepositoryInterface::class => OrderRepository::class,
        AttendeeRepositoryInterface::class => AttendeeRepository::class,
        AffiliateRepositoryInterface::class => AffiliateRepository::class,
        OrderItemRepositoryInterface::class => OrderItemRepository::class,
        QuestionRepositoryInterface::class => QuestionRepository::class,
        QuestionAnswerRepositoryInterface::class => QuestionAnswerRepository::class,
        StripePaymentsRepositoryInterface::class => StripePaymentsRepository::class,
        PromoCodeRepositoryInterface::class => PromoCodeRepository::class,
        MessageRepositoryInterface::class => MessageRepository::class,
        PasswordResetTokenRepositoryInterface::class => PasswordResetTokenRepository::class,
        PasswordResetRepositoryInterface::class => PasswordResetRepository::class,
        TaxAndFeeRepositoryInterface::class => TaxAndFeeRepository::class,
        ImageRepositoryInterface::class => ImageRepository::class,
        ProductPriceRepositoryInterface::class => ProductPriceRepository::class,
        EventStatisticRepositoryInterface::class => EventStatisticRepository::class,
        EventDailyStatisticRepositoryInterface::class => EventDailyStatisticRepository::class,
        EventSettingsRepositoryInterface::class => EventSettingsRepository::class,
        OrganizerRepositoryInterface::class => OrganizerRepository::class,
        AccountUserRepositoryInterface::class => AccountUserRepository::class,
        CapacityAssignmentRepositoryInterface::class => CapacityAssignmentRepository::class,
        StripeCustomerRepositoryInterface::class => StripeCustomerRepository::class,
        CheckInListRepositoryInterface::class => CheckInListRepository::class,
        AttendeeCheckInRepositoryInterface::class => AttendeeCheckInRepository::class,
        ProductCategoryRepositoryInterface::class => ProductCategoryRepository::class,
        InvoiceRepositoryInterface::class => InvoiceRepository::class,
        OrderRefundRepositoryInterface::class => OrderRefundRepository::class,
        WebhookRepositoryInterface::class => WebhookRepository::class,
        WebhookLogRepositoryInterface::class => WebhookLogRepository::class,
        OrderApplicationFeeRepositoryInterface::class => OrderApplicationFeeRepository::class,
        AccountConfigurationRepositoryInterface::class => AccountConfigurationRepository::class,
        QuestionAndAnswerViewRepositoryInterface::class => QuestionAndAnswerViewRepository::class,
        OutgoingMessageRepositoryInterface::class => OutgoingMessageRepository::class,
        OrganizerSettingsRepositoryInterface::class => OrganizerSettingsRepository::class,
    ];

    public function register(): void
    {
        foreach (self::$interfaceToConcreteMap as $interface => $concrete) {
            $this->app->bind($interface, $concrete);
        }
    }
}
