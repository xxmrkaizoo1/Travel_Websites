@extends('Admin.layouts.workspace')

@section('title', 'Online booking')

@section('content')
    <section class="admin-welcome">
        <div>
            <span class="admin-eyebrow">WORKSPACE</span>
            <h1>Online booking</h1>
            <p>Choose which Smart Muslim packages clients can book from the website. Open packages show a “Book now” button; closed packages keep their WhatsApp enquiry button only. New requests arrive in <a href="{{ route('admin.bookings.index', ['status' => 'pending']) }}">Bookings</a> as Pending.</p>
            <p>Available seats: how many places are still open for booking on each package. Leave it empty for no limit. When you set a booking to Confirmed, the number goes down by one for each adult and child on it; cancelling a confirmed booking puts the places back. At 0 the “Book now” button is hidden.</p>
        </div>
    </section>

    <form method="POST" action="{{ route('admin.online-booking.update') }}" data-online-booking>
        @csrf
        @method('PUT')

        @if ($sections->isNotEmpty())
            @php
                $allRows = $sections->flatMap(fn (array $section): \Illuminate\Support\Collection => $section['rows']);
            @endphp
            {{-- Filters only hide rows on screen; every package stays in the form, so saving never changes hidden ones. --}}
            <section class="admin-panel admin-online-booking-filters" aria-label="Filter packages" data-booking-filters>
                <div class="admin-online-booking-filter-row">
                    <label class="admin-search admin-online-booking-search">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="10.8" cy="10.8" r="6.8" stroke="currentColor" stroke-width="1.8"/><path d="m16 16 5 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                        <span class="visually-hidden">Search packages</span>
                        <input type="search" placeholder="Search a package, country or destination" autocomplete="off" data-booking-filter-search>
                    </label>
                    <p class="admin-online-booking-result" aria-live="polite" data-booking-filter-result>Showing all {{ $allRows->count() }} packages</p>
                </div>
                <div class="admin-online-booking-filter-row">
                    <div class="admin-filter-tabs" role="group" aria-label="Package type">
                        <button type="button" class="is-active" aria-pressed="true" data-booking-filter-type="all">All <span>{{ $allRows->count() }}</span></button>
                        @foreach ($sections as $key => $section)
                            <button type="button" aria-pressed="false" data-booking-filter-type="{{ $key }}">{{ $section['title'] }} <span>{{ $section['rows']->count() }}</span></button>
                        @endforeach
                    </div>
                    <div class="admin-filter-tabs" role="group" aria-label="Booking status and seats">
                        @foreach (['all' => 'Any status', 'open' => 'Open', 'closed' => 'Closed', 'limited' => 'Seat limit', 'full' => 'Fully booked'] as $state => $stateLabel)
                            <button type="button" @class(['is-active' => $state === 'all']) aria-pressed="{{ $state === 'all' ? 'true' : 'false' }}" data-booking-filter-state="{{ $state }}">{{ $stateLabel }}</button>
                        @endforeach
                    </div>
                </div>
            </section>
            <section class="admin-panel admin-online-booking-empty" hidden data-booking-filter-empty>
                <p class="admin-table-empty">No packages match these filters. <button class="admin-row-link" type="button" data-booking-filter-clear>Clear filters</button></p>
            </section>
        @endif

        @forelse ($sections as $key => $section)
            <section class="admin-panel admin-bookings-panel admin-online-booking" aria-labelledby="online-booking-{{ $key }}" data-booking-section="{{ $key }}">
                <div class="admin-panel-heading">
                    <div><h2 id="online-booking-{{ $key }}">{{ $section['title'] }}</h2><p>{{ $section['description'] }}</p></div>
                    <div class="admin-online-booking-bulk">
                        <button class="admin-row-link" type="button" data-booking-bulk="open">Open all</button>
                        <button class="admin-row-link" type="button" data-booking-bulk="close">Close all</button>
                    </div>
                </div>
                <div class="admin-table-wrap">
                    <table class="admin-bookings-table">
                        <thead><tr><th>Package</th><th>What clients choose</th><th>Online bookings</th><th>Available seats</th><th>Booking</th><th><span class="visually-hidden">Links</span></th></tr></thead>
                        <tbody>
                            @foreach ($section['rows'] as $row)
                                <tr data-booking-row="{{ $row['field'] }}-{{ $row['id'] }}" data-search="{{ $row['search'] }}" data-seat-state="{{ $row['seat_state'] }}">
                                    <td><strong>{{ $row['name'] }}</strong><small class="admin-cell-note">{{ $row['context'] }}</small></td>
                                    <td>{{ $row['choices'] }}</td>
                                    <td>
                                        @if ($row['bookings'] > 0)
                                            <a class="admin-row-link" href="{{ route('admin.bookings.index', ['search' => $row['name']]) }}">{{ $row['bookings'] }} {{ \Illuminate\Support\Str::plural('booking', $row['bookings']) }}</a>
                                        @else
                                            <span class="admin-cell-note">None yet</span>
                                        @endif
                                    </td>
                                    <td class="admin-seats-cell">
                                        @php
                                            $seatsField = $row['field'].'_seats.'.$row['id'];
                                            $rowDepartures = collect($row['departures']);
                                        @endphp
                                        @if ($row['per_departure'])
                                            <strong class="admin-seats-per-departure">Per departure ↓</strong>
                                            <small class="admin-cell-note">{{ $rowDepartures->whereNotNull('seats')->count() }} of {{ $rowDepartures->count() }} limited · {{ $rowDepartures->sum('taken') }} booked</small>
                                        @else
                                        <label class="admin-seats-input">
                                            <span class="visually-hidden">Available seats for {{ $row['name'] }}</span>
                                            <input type="number" name="{{ $row['field'] }}_seats[{{ $row['id'] }}]" value="{{ old($seatsField, $row['seats_left']) }}" min="0" max="100000" step="1" inputmode="numeric" placeholder="No limit" @error($seatsField) aria-invalid="true" @enderror>
                                        </label>
                                        <input type="hidden" name="{{ $row['field'] }}_seats_shown[{{ $row['id'] }}]" value="{{ old($row['field'].'_seats_shown.'.$row['id'], $row['seats_left']) }}">
                                        @error($seatsField)
                                            <small class="admin-settings-error">{{ $message }}</small>
                                        @else
                                            @if ($row['seats_left'] === 0)
                                                <small class="admin-seats-left is-full">Fully booked · {{ $row['seats_taken'] }} booked</small>
                                            @elseif ($row['seats_left'] !== null)
                                                <small @class(['admin-seats-left', 'is-low' => $row['seats_left'] <= 5])>{{ $row['seats_taken'] }} booked of {{ $row['seats'] }}</small>
                                            @else
                                                <small class="admin-cell-note">{{ $row['seats_taken'] }} booked</small>
                                            @endif
                                        @enderror
                                        @endif
                                    </td>
                                    <td>
                                        <label class="admin-booking-toggle">
                                            <input type="checkbox" name="{{ $row['field'] }}[]" value="{{ $row['id'] }}" @checked($row['open'])>
                                            <span class="admin-booking-switch" aria-hidden="true"></span>
                                            <span class="admin-booking-state" data-open-label="Open" data-closed-label="Closed">{{ $row['open'] ? 'Open' : 'Closed' }}</span>
                                            <span class="visually-hidden">Online booking for {{ $row['name'] }}</span>
                                        </label>
                                    </td>
                                    <td class="admin-online-booking-links">
                                        <a class="admin-row-link" href="{{ $row['page_url'] }}" target="_blank" rel="noreferrer">View page ↗</a>
                                    </td>
                                </tr>
                                @if ($row['per_departure'] && $row['departures'] !== [])
                                    <tr class="admin-departure-seats-row" data-booking-row-detail="{{ $row['field'] }}-{{ $row['id'] }}">
                                        <td colspan="6">
                                            <details class="admin-departure-seats" @if ($errors->has('departure_seats.'.$row['id'].'.*')) open @endif>
                                                <summary>Available seats for each departure <span>{{ count($row['departures']) }}</span></summary>
                                                @foreach (collect($row['departures'])->groupBy('destination') as $destinationName => $destinationDepartures)
                                                    <div class="admin-departure-group">
                                                        <h3>{{ $destinationName }}</h3>
                                                        <ul>
                                                            @foreach ($destinationDepartures as $departure)
                                                                @php
                                                                    $departureField = 'departure_seats.'.$row['id'].'.'.$departure['key'];
                                                                    $departurePast = $departure['travel_date'] !== null && $departure['travel_date'] < today()->toDateString();
                                                                @endphp
                                                                <li @class(['is-past' => $departurePast])>
                                                                    <span class="admin-departure-date"><strong>{{ $departure['date'] }}</strong>@if ($departure['price'] !== '')<small>{{ $departure['price'] }}</small>@endif</span>
                                                                    <label class="admin-seats-input">
                                                                        <span class="visually-hidden">Available seats for {{ $destinationName }}, {{ $departure['date'] }}</span>
                                                                        <input type="number" name="departure_seats[{{ $row['id'] }}][{{ $departure['key'] }}]" value="{{ old($departureField, $departure['left']) }}" min="0" max="100000" step="1" inputmode="numeric" placeholder="No limit" @disabled($departurePast) @error($departureField) aria-invalid="true" @enderror>
                                                                    </label>
                                                                    <input type="hidden" name="departure_seats_shown[{{ $row['id'] }}][{{ $departure['key'] }}]" value="{{ old('departure_seats_shown.'.$row['id'].'.'.$departure['key'], $departure['left']) }}" @disabled($departurePast)>
                                                                    @error($departureField)
                                                                        <small class="admin-settings-error">{{ $message }}</small>
                                                                    @else
                                                                        @if ($departurePast)
                                                                            <small class="admin-cell-note">Departed · {{ $departure['taken'] }} booked</small>
                                                                        @elseif ($departure['left'] === 0)
                                                                            <small class="admin-seats-left is-full">Fully booked · {{ $departure['taken'] }} booked</small>
                                                                        @elseif ($departure['left'] !== null)
                                                                            <small @class(['admin-seats-left', 'is-low' => $departure['left'] <= 5])>{{ $departure['taken'] }} booked of {{ $departure['seats'] }}</small>
                                                                        @else
                                                                            <small class="admin-cell-note">{{ $departure['taken'] }} booked</small>
                                                                        @endif
                                                                    @enderror
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                @endforeach
                                            </details>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        @empty
            <section class="admin-panel"><p class="admin-table-empty">There are no Smart Muslim packages with itineraries or departures to book yet.</p></section>
        @endforelse

        @if ($sections->isNotEmpty())
            <div class="admin-form-actions admin-online-booking-actions">
                <button class="admin-primary-button" type="submit">Save booking settings <span aria-hidden="true">→</span></button>
            </div>
        @endif
    </form>
@endsection
