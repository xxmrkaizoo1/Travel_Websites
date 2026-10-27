{{-- A client's booking summary. Expects $booking. --}}
@php($daysToGo = $booking->daysUntilTravel())
<a class="cp-trip" href="{{ route('client.bookings.show', $booking->reference) }}">
    <span class="cp-trip-date" aria-hidden="true">
        <strong>{{ $booking->travel_date->format('d') }}</strong>
        <small>{{ $booking->travel_date->format('M Y') }}</small>
    </span>
    <span class="cp-trip-body">
        <strong>{{ $booking->destination }}</strong>
        <small>{{ $booking->package_name ?: $booking->reference }} · {{ $booking->guestsLabel() }}</small>
        @if ($booking->status !== \App\Enums\BookingStatus::Cancelled && $daysToGo >= 0)
            <small class="cp-trip-countdown">{{ $daysToGo === 0 ? 'Departing today' : ($daysToGo === 1 ? 'Departing tomorrow' : $daysToGo.' days to go') }}</small>
        @endif
    </span>
    <span class="cp-status is-{{ $booking->status->value }}">{{ $booking->status->label() }}</span>
    <span class="cp-trip-go">@include('client.portal.partials.icon', ['name' => 'arrow'])</span>
</a>
