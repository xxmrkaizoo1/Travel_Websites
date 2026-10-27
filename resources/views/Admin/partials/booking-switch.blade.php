{{-- Open/close online booking for one package. Expects $package (TourPackagePage or ChinaMuslimPackage); $compact for list rows. --}}
@php
    $switchBookable = new \App\Support\BookablePackage($package);
    $switchOpen = $switchBookable->isOpen();
@endphp
@if ($switchBookable->isBookable())
    <form @class(['admin-booking-switch-card', 'is-open' => $switchOpen, 'is-compact' => $compact ?? false]) method="POST" action="{{ route('admin.online-booking.toggle', $switchBookable->routeKey()) }}" data-booking-switch>
        @csrf
        @method('PUT')
        <input type="hidden" name="open" value="0">
        @unless ($compact ?? false)
            <div class="admin-booking-switch-text">
                <strong>Online booking</strong>
                <span>{{ $switchOpen ? 'Open — clients see a “Book now” button and can send booking requests.' : 'Closed — the “Book now” button is hidden; clients can still enquire on WhatsApp.' }}</span>
            </div>
        @endunless
        <label class="admin-booking-toggle">
            <input type="checkbox" name="open" value="1" @checked($switchOpen)>
            <span class="admin-booking-switch" aria-hidden="true"></span>
            <span class="admin-booking-state">{{ $switchOpen ? 'Booking open' : 'Booking closed' }}</span>
            <span class="visually-hidden">Online booking for {{ $switchBookable->title() }}</span>
        </label>
        <noscript><button class="admin-secondary-button" type="submit">Save</button></noscript>
    </form>
@elseif (! ($compact ?? false))
    <div class="admin-booking-switch-card is-unavailable">
        <div class="admin-booking-switch-text">
            <strong>Online booking</strong>
            <span>Not available yet. Add an itinerary or a destination with departures to let clients book this page online.</span>
        </div>
    </div>
@endif
