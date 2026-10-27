@extends('client.portal.layout')

@section('title', 'Overview')

@section('content')
    <section class="cp-welcome">
        <div>
            <span class="cp-eyebrow">MY ACCOUNT · {{ strtoupper(today()->format('l, j F')) }}</span>
            <h1>Welcome back, {{ \Illuminate\Support\Str::of($client->name)->explode(' ')->first() }}!</h1>
            <p>Your trips, requests and account details in one place.</p>
        </div>
        <div class="cp-welcome-actions" data-tour="actions">
            <a class="cp-button cp-button--glass" href="{{ route('smart-muslim') }}">@include('client.portal.partials.icon', ['name' => 'compass']) Browse packages</a>
            <a class="cp-button cp-button--light" href="{{ route('client.enquiries.index') }}#new-request">@include('client.portal.partials.icon', ['name' => 'plus']) Plan a new trip</a>
        </div>
    </section>

    @if ($nextTrip)
        <section class="cp-next-trip" aria-labelledby="next-trip-title">
            @if ($nextTripImage)
                <img src="{{ $nextTripImage }}" alt="" loading="lazy">
            @endif
            <div class="cp-next-trip-body">
                <span class="cp-eyebrow cp-eyebrow--light">YOUR NEXT TRIP</span>
                <h2 id="next-trip-title">{{ $nextTrip->destination }}</h2>
                <p>{{ $nextTrip->package_name ?: 'Booking '.$nextTrip->reference }}</p>
                <ul>
                    <li>@include('client.portal.partials.icon', ['name' => 'calendar']) {{ $nextTrip->travel_date->format('D, d M Y') }}@if ($nextTrip->return_date) – {{ $nextTrip->return_date->format('d M Y') }}@endif</li>
                    <li>@include('client.portal.partials.icon', ['name' => 'users']) {{ $nextTrip->guestsLabel() }}</li>
                    <li><span class="cp-status is-{{ $nextTrip->status->value }}">{{ $nextTrip->status->label() }}</span></li>
                </ul>
                <a class="cp-button cp-button--light" href="{{ route('client.bookings.show', $nextTrip->reference) }}">View trip details @include('client.portal.partials.icon', ['name' => 'arrow'])</a>
            </div>
            <div class="cp-countdown" aria-label="{{ $nextTrip->daysUntilTravel() }} days to go">
                <strong>{{ $nextTrip->daysUntilTravel() }}</strong>
                <span>{{ $nextTrip->daysUntilTravel() === 1 ? 'day' : 'days' }} to go</span>
            </div>
        </section>
    @else
        <section class="cp-start" aria-labelledby="start-title">
            <div class="cp-start-intro">
                <span class="cp-start-icon">@include('client.portal.partials.icon', ['name' => 'suitcase'])</span>
                <h2 id="start-title">No upcoming trips yet</h2>
                <p>Where would you like to go next? Book one of our packages online, or tell us your plans and we will put a trip together for you.</p>
            </div>
            <div class="cp-start-options">
                <a class="cp-start-option" href="{{ route('smart-muslim') }}">
                    <span class="cp-start-option-icon">@include('client.portal.partials.icon', ['name' => 'compass'])</span>
                    <strong>Book a package</strong>
                    <span>Series departures, private tours and China Muslim tours with halal meals.</span>
                    <em>Browse packages @include('client.portal.partials.icon', ['name' => 'arrow'])</em>
                </a>
                <a class="cp-start-option" href="{{ route('client.enquiries.index') }}#new-request">
                    <span class="cp-start-option-icon cp-start-option-icon--violet">@include('client.portal.partials.icon', ['name' => 'chat'])</span>
                    <strong>Send a custom request</strong>
                    <span>Share your destination, dates and travellers. Our team replies with a plan.</span>
                    <em>Send a trip request @include('client.portal.partials.icon', ['name' => 'arrow'])</em>
                </a>
            </div>
        </section>
    @endif

    <section class="cp-stats" aria-label="Account summary" data-tour="stats">
        @foreach ([
            ['calendar', 'Upcoming trips', $stats['upcoming'], 'blue'],
            ['suitcase', 'Trips booked', $stats['trips'], 'green'],
            ['users', 'Travellers', $stats['travellers'], 'amber'],
            ['chat', 'Open requests', $stats['openRequests'], 'violet'],
        ] as [$icon, $label, $value, $tone])
            <article class="cp-stat cp-stat--{{ $tone }}">
                <span class="cp-stat-icon">@include('client.portal.partials.icon', ['name' => $icon])</span>
                <div><span>{{ $label }}</span><strong data-count-up="{{ $value }}">{{ $value }}</strong></div>
            </article>
        @endforeach
    </section>

    <div class="cp-columns">
        <section class="cp-panel">
            <header><h2>More upcoming trips</h2><a href="{{ route('client.bookings.index') }}">All bookings →</a></header>
            @forelse ($upcoming as $booking)
                @include('client.portal.partials.booking-card', ['booking' => $booking])
            @empty
                <div class="cp-empty">
                    <span class="cp-empty-icon">@include('client.portal.partials.icon', ['name' => 'plane'])</span>
                    <p>{{ $nextTrip ? 'Your next trip is the only one coming up.' : 'Nothing booked yet. Your trips will appear here.' }}</p>
                </div>
            @endforelse
        </section>

        <section class="cp-panel">
            <header><h2>Latest requests</h2><a href="{{ route('client.enquiries.index') }}">All requests →</a></header>
            @forelse ($requests as $tripRequest)
                @include('client.portal.partials.request-card', ['tripRequest' => $tripRequest])
            @empty
                <div class="cp-empty">
                    <span class="cp-empty-icon">@include('client.portal.partials.icon', ['name' => 'chat'])</span>
                    <p>You have not sent any trip requests yet.</p>
                </div>
            @endforelse
        </section>
    </div>
@endsection
