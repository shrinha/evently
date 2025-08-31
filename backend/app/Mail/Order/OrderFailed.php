<?php

namespace Evently\Mail\Order;

use Evently\DomainObjects\EventDomainObject;
use Evently\DomainObjects\EventSettingDomainObject;
use Evently\DomainObjects\OrderDomainObject;
use Evently\DomainObjects\OrganizerDomainObject;
use Evently\Helper\Url;
use Evently\Mail\BaseMail;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * @uses /backend/resources/views/emails/orders/order-failed.blade.php
 */
class OrderFailed extends BaseMail
{
    public function __construct(
        private readonly OrderDomainObject        $order,
        private readonly EventDomainObject        $event,
        private readonly OrganizerDomainObject    $organizer,
        private readonly EventSettingDomainObject $eventSettings,
    )
    {
        parent::__construct();
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: $this->eventSettings->getSupportEmail(),
            subject: __('Your order wasn\'t successful'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.orders.order-failed',
            with: [
                'event' => $this->event,
                'order' => $this->order,
                'organizer' => $this->organizer,
                'eventSettings' => $this->eventSettings,
                'eventUrl' => sprintf(
                    Url::getFrontEndUrlFromConfig(Url::EVENT_HOMEPAGE),
                    $this->event->getId(),
                    $this->event->getSlug(),
                )
            ]
        );
    }
}
