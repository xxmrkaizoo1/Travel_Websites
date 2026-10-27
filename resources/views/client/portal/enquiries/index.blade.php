@extends('client.portal.layout')

@section('title', 'Trip requests')

@section('content')
    <section class="cp-welcome">
        <div>
            <span class="cp-eyebrow">MY ACCOUNT</span>
            <h1>Trip requests</h1>
            <p>Tell us about the trip you have in mind. Our team replies by phone, WhatsApp or email, and you can follow each request here.</p>
        </div>
    </section>

    <div class="cp-columns cp-columns--form">
        <section class="cp-panel" id="new-request" aria-labelledby="new-request-title" data-tour="request-form">
            <header><h2 id="new-request-title"><span class="cp-panel-icon">@include('client.portal.partials.icon', ['name' => 'plus'])</span>{{ $aboutBooking ? 'Ask about booking '.$aboutBooking->reference : 'Plan a new trip' }}</h2></header>

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

            <form class="cp-form" method="POST" action="{{ route('client.enquiries.store') }}">
                @csrf
                <label class="cp-field cp-field--wide">
                    <span>Where would you like to go?</span>
                    <input name="destination" list="trip-destinations" value="{{ old('destination', $aboutBooking?->destination) }}" required maxlength="120" placeholder="e.g. Melbourne, Bali or Seoul">
                    <datalist id="trip-destinations">
                        @foreach ($destinations as $destinationName)
                            <option value="{{ $destinationName }}"></option>
                        @endforeach
                    </datalist>
                </label>
                <label class="cp-field"><span>Preferred travel date <small>Optional</small></span><input type="date" name="preferred_date" value="{{ old('preferred_date') }}" min="{{ today()->toDateString() }}"></label>
                <div class="cp-field-pair">
                    <label class="cp-field"><span>Adults</span><input type="number" name="adults" value="{{ old('adults', $aboutBooking?->adults ?? 2) }}" required min="1" max="200"></label>
                    <label class="cp-field"><span>Children</span><input type="number" name="children" value="{{ old('children', $aboutBooking?->children ?? 0) }}" min="0" max="200"></label>
                </div>
                <label class="cp-field cp-field--wide">
                    <span>Tell us more</span>
                    <textarea name="message" required maxlength="1500" rows="5" placeholder="Budget, hotel preferences, halal meals, special requests…">{{ old('message', $aboutBooking ? "About my booking {$aboutBooking->reference}: " : '') }}</textarea>
                </label>
                <p class="cp-form-note">We will contact you at {{ auth()->user()->phone ?: auth()->user()->email }}.</p>
                <button class="cp-button" type="submit">Send request <span aria-hidden="true">→</span></button>
            </form>
        </section>

        <section class="cp-panel" aria-labelledby="request-history-title" data-tour="request-history">
            <header><h2 id="request-history-title"><span class="cp-panel-icon">@include('client.portal.partials.icon', ['name' => 'chat'])</span>Your requests</h2></header>
            @forelse ($enquiries as $tripRequest)
                @include('client.portal.partials.request-card', ['tripRequest' => $tripRequest])
            @empty
                <div class="cp-empty">
                    <span class="cp-empty-icon">@include('client.portal.partials.icon', ['name' => 'chat'])</span>
                    <p>No requests yet. Your first one will appear here.</p>
                </div>
            @endforelse
            {{ $enquiries->links('Admin.partials.pagination') }}
        </section>
    </div>
@endsection
