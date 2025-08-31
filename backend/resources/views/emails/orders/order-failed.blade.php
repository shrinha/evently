@php /** @var \Evently\DomainObjects\OrderDomainObject $order */ @endphp
@php /** @var \Evently\DomainObjects\OrganizerDomainObject $organizer */ @endphp
@php /** @var \Evently\DomainObjects\EventDomainObject $event */ @endphp
@php /** @var \Evently\DomainObjects\EventSettingDomainObject $eventSettings */ @endphp
@php /** @var string $eventUrl */ @endphp

@php /** @see \Evently\Mail\Order\OrderFailed */ @endphp

<x-mail::message>
{{ __('Hello') }},

{{ __('Your recent order for') }} <b>{{$event->getTitle()}}</b> {{ __('was not successful.') }}

<x-mail::button :url="$eventUrl">
{{ __('View Event Homepage') }}
</x-mail::button>

{{ __('If you have any questions or need assistance, feel free to reach out to our support team') }}
{{ __('at') }} {{ $supportEmail ?? 'hello@Evently' }}.

{{ __('Best regards') }},<br>
{{ $organizer->getName() ?: config('app.name') }}

{!! $eventSettings->getGetEmailFooterHtml() !!}
</x-mail::message>
