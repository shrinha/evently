@php /** @var \Evently\DomainObjects\OrderDomainObject $order */ @endphp
@php /** @var \Evently\DomainObjects\EventDomainObject $event */ @endphp
@php /** @var \Evently\DomainObjects\OrganizerDomainObject $organizer */ @endphp
@php /** @var \Evently\Values\MoneyValue $refundAmount */ @endphp
@php /** @var \Evently\DomainObjects\EventSettingDomainObject $eventSettings */ @endphp

@php /** @see \Evently\Mail\Order\OrderRefunded */ @endphp

<x-mail::message>
{{ __('Hello') }},

{{ __('You have received a refund of :refundAmount for the following event: :eventTitle.', ['refundAmount' => $refundAmount, 'eventTitle' => $event->getTitle()]) }}

{{ __('Thank you') }},<br>
{{ $organizer->getName() ?: config('app.name') }}

{!! $eventSettings->getGetEmailFooterHtml() !!}
</x-mail::message>
