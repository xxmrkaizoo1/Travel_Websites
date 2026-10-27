@extends('Admin.layouts.workspace')

@section('title', 'Online booking')

@section('content')
    <section class="admin-welcome">
        <div>
            <span class="admin-eyebrow">WORKSPACE</span>
            <h1>Online booking</h1>
            <p>Choose which Smart Muslim packages clients can book from the website. Open packages show a “Book now” button; closed packages keep their WhatsApp enquiry button only. New requests arrive in <a href="{{ route('admin.bookings.index', ['status' => 'pending']) }}">Bookings</a> as Pending.</p>
        </div>
    </section>

    <form method="POST" action="{{ route('admin.online-booking.update') }}" data-online-booking>
        @csrf
        @method('PUT')

        @forelse ($sections as $key => $section)
            <section class="admin-panel admin-bookings-panel admin-online-booking" aria-labelledby="online-booking-{{ $key }}">
                <div class="admin-panel-heading">
                    <div><h2 id="online-booking-{{ $key }}">{{ $section['title'] }}</h2><p>{{ $section['description'] }}</p></div>
                    <div class="admin-online-booking-bulk">
                        <button class="admin-row-link" type="button" data-booking-bulk="open">Open all</button>
                        <button class="admin-row-link" type="button" data-booking-bulk="close">Close all</button>
                    </div>
                </div>
                <div class="admin-table-wrap">
                    <table class="admin-bookings-table">
                        <thead><tr><th>Package</th><th>What clients choose</th><th>Online bookings</th><th>Booking</th><th><span class="visually-hidden">Links</span></th></tr></thead>
                        <tbody>
                            @foreach ($section['rows'] as $row)
                                <tr>
                                    <td><strong>{{ $row['name'] }}</strong><small class="admin-cell-note">{{ $row['context'] }}</small></td>
                                    <td>{{ $row['choices'] }}</td>
                                    <td>
                                        @if ($row['bookings'] > 0)
                                            <a class="admin-row-link" href="{{ route('admin.bookings.index', ['search' => $row['name']]) }}">{{ $row['bookings'] }} {{ \Illuminate\Support\Str::plural('booking', $row['bookings']) }}</a>
                                        @else
                                            <span class="admin-cell-note">None yet</span>
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
