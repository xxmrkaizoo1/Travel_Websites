@extends('Admin.layouts.workspace')

@php
    $isNew = ! $booking->exists;
    $breadcrumbParent = ['Bookings', route('admin.bookings.index')];
    $bookedPackage = $booking->bookable ? new \App\Support\BookablePackage($booking->bookable) : null;
@endphp

@section('title', $isNew ? 'New booking' : 'Booking '.$booking->reference)

@section('content')
    <section class="admin-welcome">
        <div>
            <span class="admin-eyebrow">BOOKINGS</span>
            <h1>{{ $isNew ? 'New booking' : 'Booking '.$booking->reference }}</h1>
            <p>
                @if ($isNew)
                    Record a trip for one of your clients. The booking reference is created when you save.
                @else
                    Created {{ $booking->created_at->format('d M Y, g:i a') }} · last updated {{ $booking->updated_at->diffForHumans() }}
                @endif
            </p>
        </div>
        @unless ($isNew)
            <a class="admin-secondary-button" href="{{ route('admin.clients.show', $booking->user_id) }}">View client <span aria-hidden="true">→</span></a>
        @endunless
    </section>

    @if ($enquiry)
        <aside class="admin-note-card">
            <strong>From {{ $enquiry->name }}’s enquiry · {{ $enquiry->created_at->format('d M Y') }}</strong>
            <p>{{ $enquiry->message }}</p>
            <small>{{ collect([$enquiry->email, $enquiry->phone])->filter()->implode(' · ') }}. Saving this booking closes the enquiry and links it to the booking.</small>
        </aside>
    @endif

    @if (! $isNew && $booking->wasBookedOnline())
        <aside class="admin-note-card">
            <strong>Booked online by the client · {{ $booking->created_at->format('d M Y, g:i a') }}</strong>
            <p>From @if ($bookedPackage)<a href="{{ $bookedPackage->pageUrl() }}" target="_blank" rel="noreferrer">{{ $bookedPackage->title() }} ↗</a>.@else a package that has since been removed.@endif @if ($booking->amount !== null && $booking->status === \App\Enums\BookingStatus::Pending) The amount is an estimate from the package price; adjust it before confirming.@endif</p>
            @if ($booking->special_requests)
                <p><strong>Special requests:</strong> {{ $booking->special_requests }}</p>
            @endif
            <small>Clients see this booking as “Requested” until you confirm it.</small>
        </aside>
    @endif

    @if (! $isNew && $booking->enquiry)
        <aside class="admin-note-card">
            <strong>Converted from {{ $booking->enquiry->name }}’s enquiry · {{ $booking->enquiry->created_at->format('d M Y') }}</strong>
            <p>{{ $booking->enquiry->message }}</p>
        </aside>
    @endif

    <form class="admin-panel admin-record-form" method="POST" action="{{ $isNew ? route('admin.bookings.store') : route('admin.bookings.update', $booking) }}">
        @csrf
        @unless ($isNew)
            @method('PUT')
        @endunless
        @if ($enquiry)
            <input type="hidden" name="enquiry_id" value="{{ $enquiry->id }}">
        @endif

        <fieldset class="admin-form-section">
            <legend>Client</legend>
            <div class="homepage-form-grid">
                <label class="homepage-form-field homepage-form-field-wide">
                    <span>Client account <small class="homepage-field-hint">Not listed? <a href="{{ route('admin.clients.create') }}" target="_blank" rel="noreferrer">Add a client account ↗</a>, then reload this page</small></span>
                    <select name="user_id" required>
                        <option value="">Choose a client…</option>
                        @foreach ($clients as $client)
                            <option value="{{ $client->id }}" @selected((int) old('user_id', $booking->user_id) === $client->id)>{{ $client->name }} · {{ $client->email }}{{ $client->isSuspended() ? ' (suspended)' : '' }}</option>
                        @endforeach
                    </select>
                </label>
            </div>
        </fieldset>

        <fieldset class="admin-form-section">
            <legend>Trip</legend>
            <div class="homepage-form-grid">
                <label class="homepage-form-field">
                    <span>Destination <small class="homepage-field-hint">Pick from the list or type your own</small></span>
                    <input name="destination" list="booking-destinations" value="{{ old('destination', $booking->destination) }}" required maxlength="120">
                    <datalist id="booking-destinations">
                        @foreach ($destinations as $destinationName)
                            <option value="{{ $destinationName }}"></option>
                        @endforeach
                    </datalist>
                </label>
                <label class="homepage-form-field"><span>Package <small class="homepage-field-hint">Optional · e.g. Melbourne 7D6N with Brunei City Tour</small></span><input name="package_name" value="{{ old('package_name', $booking->package_name) }}" maxlength="160"></label>
                <label class="homepage-form-field"><span>Travel date</span><input type="date" name="travel_date" value="{{ old('travel_date', $booking->travel_date?->format('Y-m-d')) }}" required></label>
                <label class="homepage-form-field"><span>Return date <small class="homepage-field-hint">Optional</small></span><input type="date" name="return_date" value="{{ old('return_date', $booking->return_date?->format('Y-m-d')) }}"></label>
                <label class="homepage-form-field"><span>Adults</span><input type="number" name="adults" value="{{ old('adults', $booking->adults) }}" required min="1" max="200"></label>
                <label class="homepage-form-field"><span>Children</span><input type="number" name="children" value="{{ old('children', $booking->children) }}" min="0" max="200"></label>
            </div>
        </fieldset>

        <fieldset class="admin-form-section">
            <legend>Payment &amp; status</legend>
            <div class="homepage-form-grid">
                <label class="homepage-form-field"><span>Amount (RM) <small class="homepage-field-hint">Total for the whole booking · required once confirmed</small></span><input type="number" name="amount" value="{{ old('amount', $booking->amount) }}" min="0" max="9999999.99" step="0.01" inputmode="decimal"></label>
                <label class="homepage-form-field">
                    <span>Status <small class="homepage-field-hint">Confirmed and completed bookings count as revenue</small></span>
                    <select name="status" required>
                        @foreach (\App\Enums\BookingStatus::cases() as $status)
                            <option value="{{ $status->value }}" @selected(old('status', $booking->status?->value) === $status->value)>{{ $status->label() }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="homepage-form-field homepage-form-field-wide"><span>Notes <small class="homepage-field-hint">Optional · only visible to the admin team</small></span><textarea name="notes" maxlength="2000" rows="4">{{ old('notes', $booking->notes) }}</textarea></label>
            </div>
        </fieldset>

        <div class="admin-form-actions">
            <a class="admin-secondary-button" href="{{ route('admin.bookings.index') }}">Cancel</a>
            <button class="admin-primary-button" type="submit">{{ $isNew ? 'Create booking' : 'Save changes' }} <span aria-hidden="true">→</span></button>
        </div>
    </form>

    @unless ($isNew)
        <form class="admin-danger-zone" method="POST" action="{{ route('admin.bookings.destroy', $booking) }}" data-confirm="Delete booking {{ $booking->reference }}? This cannot be undone.">
            @csrf
            @method('DELETE')
            <div><strong>Delete this booking</strong><p>Removes the booking and its revenue from reports. To keep a record, set the status to Cancelled instead.</p></div>
            <button class="admin-danger-button" type="submit">Delete booking</button>
        </form>
    @endunless
@endsection
