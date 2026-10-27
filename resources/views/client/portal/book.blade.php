@extends('client.portal.layout')

@section('title', 'Book '.$bookable->title())

@section('content')
    @php
        $optionGroups = collect($options)->groupBy(fn (array $option): string => (string) $option['group']);
        $chosen = collect($options)->firstWhere('key', $selectedOption) ?? $options[0];
        $travellers = max(1, (int) old('adults', 2) + (int) old('children', 0));
    @endphp

    <a class="cp-back" href="{{ $bookable->pageUrl() }}">← Back to {{ $bookable->title() }}</a>

    <section class="cp-booking-hero">
        <img src="{{ $bookable->imageUrl() }}" alt="" loading="lazy">
        <div>
            <span class="cp-eyebrow cp-eyebrow--light">BOOK ONLINE</span>
            <h1>{{ $bookable->title() }}</h1>
            <p>Choose your package, date and travellers. We will confirm availability and the final price with you.</p>
        </div>
    </section>

    @if (! $bookable->isOpen())
        <section class="cp-panel cp-help-panel">
            <div>
                <h2>Online booking is closed for this package</h2>
                <p>You can still book with our team. Send us a trip request or message us on WhatsApp.</p>
            </div>
            <div class="cp-help-actions">
                <a class="cp-button" href="{{ route('client.enquiries.index') }}#new-request">Send a trip request</a>
                <a class="cp-button cp-button--ghost" href="https://wa.me/60165500590?text={{ rawurlencode('Hi, I would like to book '.$bookable->title().'.') }}" target="_blank" rel="noreferrer">WhatsApp us ↗</a>
            </div>
        </section>
    @else
        <div class="cp-columns cp-columns--form">
            <section class="cp-panel" aria-labelledby="booking-form-title">
                <header><h2 id="booking-form-title">Your booking</h2></header>

                @if ($errors->any())
                    <div class="cp-errors" role="alert">
                        <strong>Please check the form.</strong>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form class="cp-form" method="POST" action="{{ route('client.package-bookings.store', $bookable->routeKey()) }}" data-package-booking>
                    @csrf
                    <label class="cp-field cp-field--wide">
                        <span>{{ $optionGroups->keys()->filter()->isNotEmpty() ? 'Destination & departure' : 'Package' }}</span>
                        <select name="option" required data-booking-option>
                            @foreach ($optionGroups as $groupName => $groupOptions)
                                @if ($groupName !== '')<optgroup label="{{ $groupName }}">@endif
                                @foreach ($groupOptions as $option)
                                    <option value="{{ $option['key'] }}" data-date="{{ $option['travel_date'] }}" data-price="{{ $option['price'] }}" @selected($option['key'] === $selectedOption)>{{ $option['label'] }}</option>
                                @endforeach
                                @if ($groupName !== '')</optgroup>@endif
                            @endforeach
                        </select>
                    </label>
                    <label class="cp-field">
                        <span>Travel date <small data-booking-date-hint>{{ $chosen['travel_date'] ? 'Set by the departure you chose' : 'Choose the day you would like to leave' }}</small></span>
                        <input type="date" name="travel_date" value="{{ $chosen['travel_date'] ?? old('travel_date') }}" min="{{ today()->toDateString() }}" @readonly($chosen['travel_date']) @required(! $chosen['travel_date']) data-booking-date>
                    </label>
                    <div class="cp-field-pair">
                        <label class="cp-field"><span>Adults</span><input type="number" name="adults" value="{{ old('adults', 2) }}" required min="1" max="50" data-booking-adults></label>
                        <label class="cp-field"><span>Children</span><input type="number" name="children" value="{{ old('children', 0) }}" min="0" max="50" data-booking-children></label>
                    </div>
                    <label class="cp-field cp-field--wide">
                        <span>Special requests <small>Optional</small></span>
                        <textarea name="special_requests" maxlength="1500" rows="4" placeholder="Room sharing, dietary needs, wheelchair access, passport names…">{{ old('special_requests') }}</textarea>
                    </label>
                    <p class="cp-form-note">We will contact you at {{ auth()->user()->phone ?: auth()->user()->email }} to confirm. Nothing is charged online.</p>
                    <button class="cp-button" type="submit">Send booking request <span aria-hidden="true">→</span></button>
                </form>
            </section>

            <aside class="cp-panel cp-booking-summary" aria-labelledby="booking-summary-title">
                <header><h2 id="booking-summary-title">Estimated total</h2></header>
                <p class="cp-booking-estimate" data-booking-estimate data-empty="Price on request">
                    {{ $chosen['price'] ? 'RM '.number_format($chosen['price'] * $travellers) : 'Price on request' }}
                </p>
                <p class="cp-form-note" data-booking-estimate-note>{{ $chosen['price'] ? 'RM '.number_format($chosen['price']).' per person × '.$travellers.' '.($travellers === 1 ? 'traveller' : 'travellers') : 'Our team will quote the price for your dates.' }}</p>
                <ol class="cp-booking-steps">
                    <li><strong>Send your request</strong><span>It appears in My bookings as “Requested”.</span></li>
                    <li><strong>We confirm</strong><span>Our team checks seats and confirms the final price with you.</span></li>
                    <li><strong>Get ready to travel</strong><span>Your booking turns “Confirmed” and the countdown begins.</span></li>
                </ol>
            </aside>
        </div>
    @endif
@endsection
