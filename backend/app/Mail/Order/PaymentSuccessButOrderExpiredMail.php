<?php

namespace Evently\Mail\Order;

use Evently\DomainObjects\EventDomainObject;
use Evently\DomainObjects\EventSettingDomainObject;
use Evently\DomainObjects\OrderDomainObject;
use Evently\DomainObjects\OrganizerDomainObject;
use Evently\Mail\BaseMail;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * @uses /backend/resources/views/emails/orders/payment-success-but-order-expired.blade.php
 */
class PaymentSuccessButOrderExpiredMail extends BaseMail
{
    public function __construct(
        private readonly OrderDomainObject        $order,
        private readonly EventDomainObject        $event,
        private readonly EventSettingDomainObject $eventSettings,
        private readonly OrganizerDomainObject    $organizer,
    )
    {
        parent::__construct();
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: $this->eventSettings->getSupportEmail(),
            subject: __('We were unable to process your order'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.orders.payment-success-but-order-expired',
            with: [
                'event' => $this->event,
                'order' => $this->order,
                'organizer' => $this->organizer,
                'eventSettings' => $this->eventSettings,
            ]
        );
    }
}
