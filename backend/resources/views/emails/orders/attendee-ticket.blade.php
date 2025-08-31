@php use Evently\Helper\DateHelper; @endphp
@php /** @uses \Evently\Mail\Order\OrderSummary */ @endphp
@php /** @var \Evently\DomainObjects\EventDomainObject $event */ @endphp
@php /** @var \Evently\DomainObjects\EventSettingDomainObject $eventSettings */ @endphp
@php /** @var \Evently\DomainObjects\OrganizerDomainObject $organizer */ @endphp
@php /** @var \Evently\DomainObjects\AttendeeDomainObject $attendee */ @endphp
@php /** @var \Evently\DomainObjects\OrderDomainObject $order */ @endphp

@php /** @var string $ticketUrl */ @endphp
@php /** @see \Evently\Mail\Attendee\AttendeeTicketMail */ @endphp

<x-mail::message>
# {{ __('You\'re going to') }} {{ $event->getTitle() }}! 🎉
<br>
<br>
@if($order->isOrderAwaitingOfflinePayment())
<div style="border-radius: 4px; background-color: #f8d7da; color: #842029; margin-bottom: 1.5rem; padding: 1rem;">
<p>
{{ __('ℹ️ Your order is pending payment. Tickets have been issued but will not be valid until payment is received.') }}
</p>
</div>
@endif

{{ __('Please find your ticket details below.') }}

<x-mail::button :url="$ticketUrl">
{{ __('View Ticket') }}
</x-mail::button>

{{ __('If you have any questions or need assistance, please reply to this email or contact the event organizer') }}
{{ __('at') }} <a href="mailto:{{$eventSettings->getSupportEmail()}}">{{$eventSettings->getSupportEmail()}}</a>.

{{ __('Best regards,') }}<br>
{{ $organizer->getName() ?: config('app.name') }}

</x-mail::message>
