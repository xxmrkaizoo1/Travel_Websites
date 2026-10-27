@extends('client.portal.layout')

@section('title', 'Booking '.$booking->reference)

@section('content')
    @php
        $steps = ['Requested', 'Confirmed', 'Completed'];
        $reached = match ($booking->status) {
            \App\Enums\BookingStatus::Pending => 1,
            \App\Enums\BookingStatus::Confirmed => 2,
            \App\Enums\BookingStatus::Completed => 3,
            \App\Enums\BookingStatus::Cancelled => 0,
        };
        $daysToGo = $booking->daysUntilTravel();
    @endphp

    <a class="cp-back" href="{{ route('client.bookings.index') }}">← My bookings</a>

    <section class="cp-booking-hero">
        @if ($image)
            <img src="{{ $image }}" alt="" loading="lazy">
        @endif
        <div>
            <span class="cp-eyebrow cp-eyebrow--light">BOOKING {{ $booking->reference }}</span>
            <h1>{{ $booking->destination }}</h1>
            @if ($booking->package_name)
                <p>{{ $booking->package_name }}</p>
            @endif
            <span class="cp-status is-{{ $booking->status->value }}">{{ $booking->status->label() }}</span>
        </div>
    </section>

    @if ($booking->status === \App\Enums\BookingStatus::Cancelled)
        <p class="cp-notice cp-notice--cancelled">This booking was cancelled. Contact us if you would like to rebook.</p>
    @else
        <ol class="cp-progress" aria-label="Booking progress">
            @foreach ($steps as $index => $step)
                <li @class(['is-done' => $index < $reached, 'is-current' => $index === $reached - 1])>
                    <span aria-hidden="true">{{ $index < $reached ? '✓' : $index + 1 }}</span>{{ $step }}
                </li>
            @endforeach
        </ol>
    @endif

    <section class="cp-panel">
        <header><h2>Trip details</h2></header>
        <dl class="cp-details">
            <div><dt>Departure</dt><dd>{{ $booking->travel_date->format('l, d F Y') }}</dd></div>
            <div><dt>Return</dt><dd>{{ $booking->return_date?->format('l, d F Y') ?? 'To be confirmed' }}</dd></div>
            <div><dt>Travellers</dt><dd>{{ $booking->guestsLabel() }}</dd></div>
            <div><dt>{{ $booking->status === \App\Enums\BookingStatus::Pending ? 'Estimated total' : 'Total price' }}</dt><dd>{{ $booking->amount === null ? 'To be confirmed' : 'RM '.number_format((float) $booking->amount, 2) }}</dd></div>
            @if ($booking->status !== \App\Enums\BookingStatus::Cancelled && $daysToGo >= 0)
                <div><dt>Countdown</dt><dd>{{ $daysToGo === 0 ? 'Departing today' : $daysToGo.' '.($daysToGo === 1 ? 'day' : 'days').' to go' }}</dd></div>
            @endif
            <div><dt>Booked on</dt><dd>{{ $booking->created_at->format('d F Y') }}</dd></div>
        </dl>
        @if ($booking->special_requests)
            <p class="cp-special-requests"><strong>Your special requests</strong>{{ $booking->special_requests }}</p>
        @endif
    </section>

    <section class="cp-panel cp-help-panel">
        <div>
            <h2>Questions about this trip?</h2>
            <p>Ask to change dates, add travellers or anything else. Mention your booking reference {{ $booking->reference }}.</p>
        </div>
        <div class="cp-help-actions">
            <a class="cp-button" href="{{ route('client.enquiries.index', ['booking' => $booking->reference]) }}#new-request">Send a request</a>
            <a class="cp-button cp-button--ghost" href="https://wa.me/60165500590?text={{ rawurlencode("Hi, I have a question about my booking {$booking->reference} ({$booking->destination}).") }}" target="_blank" rel="noreferrer">WhatsApp us ↗</a>
        </div>
    </section>
@endsection
