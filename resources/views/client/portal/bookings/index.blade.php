@extends('client.portal.layout')

@section('title', 'My bookings')

@section('content')
    <section class="cp-welcome">
        <div>
            <span class="cp-eyebrow">MY ACCOUNT</span>
            <h1>My bookings</h1>
            <p>Every trip you have booked with us. Open a booking for its full details and progress.</p>
        </div>
    </section>

    <nav class="cp-tabs" aria-label="Filter bookings" data-tour="bookings-tabs">
        @foreach (['upcoming' => 'Upcoming', 'past' => 'Past trips', 'cancelled' => 'Cancelled', 'all' => 'All'] as $tabKey => $tabLabel)
            <a @class(['is-active' => $tab === $tabKey]) href="{{ route('client.bookings.index', $tabKey === 'upcoming' ? [] : ['tab' => $tabKey]) }}" @if ($tab === $tabKey) aria-current="page" @endif>{{ $tabLabel }}</a>
        @endforeach
    </nav>

    <section class="cp-panel" data-tour="bookings-list">
        @forelse ($bookings as $booking)
            @include('client.portal.partials.booking-card', ['booking' => $booking])
        @empty
            <div class="cp-empty">
                <span class="cp-empty-icon">@include('client.portal.partials.icon', ['name' => 'plane'])</span>
                <p>
                    {{ match ($tab) {
                        'upcoming' => 'No upcoming trips.',
                        'past' => 'No past trips yet.',
                        'cancelled' => 'No cancelled bookings.',
                        default => 'No bookings yet.',
                    } }}
                </p>
                <div class="cp-empty-actions">
                    <a class="cp-button" href="{{ route('smart-muslim') }}">Browse packages</a>
                    <a class="cp-button cp-button--ghost" href="{{ route('client.enquiries.index') }}#new-request">Plan a new trip</a>
                </div>
            </div>
        @endforelse

        {{ $bookings->links('Admin.partials.pagination') }}
    </section>
@endsection
