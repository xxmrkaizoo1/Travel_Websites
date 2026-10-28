{{-- Full-screen bookings view opened from the dashboard's Recent bookings panel. Expects $bookings (with client loaded) and $total. --}}
@php
    $avatarTones = ['sky', 'lilac', 'peach', 'mint'];
    $statusCounts = $bookings->countBy(fn (\App\Models\Booking $booking): string => $booking->status->value);
@endphp
<dialog class="admin-booking-manager" aria-labelledby="booking-manager-title" data-booking-manager data-token="{{ csrf_token() }}" data-total="{{ number_format($total) }}">
    <div class="admin-booking-manager-shell">
        <header class="admin-booking-manager-head">
            <div>
                <span class="admin-eyebrow">BOOKINGS</span>
                <h2 id="booking-manager-title">Manage bookings</h2>
                <p>Change a status straight from the list; it saves at once. Open a booking to edit its details.</p>
            </div>
            <div class="admin-booking-manager-actions">
                <a class="admin-primary-button" href="{{ route('admin.bookings.create') }}"><span aria-hidden="true">＋</span> New booking</a>
                <button class="admin-booking-manager-close" type="button" data-booking-manager-close aria-label="Close full screen">✕</button>
            </div>
        </header>

        <div class="admin-booking-manager-toolbar">
            <div class="admin-filter-tabs" role="group" aria-label="Filter by status">
                <button type="button" class="is-active" aria-pressed="true" data-manager-status="all">All <span data-manager-count="all">{{ $bookings->count() }}</span></button>
                @foreach (\App\Enums\BookingStatus::cases() as $status)
                    <button type="button" aria-pressed="false" data-manager-status="{{ $status->value }}">{{ $status->label() }} <span data-manager-count="{{ $status->value }}">{{ $statusCounts[$status->value] ?? 0 }}</span></button>
                @endforeach
            </div>
            <label class="admin-search admin-booking-manager-search">
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="10.8" cy="10.8" r="6.8" stroke="currentColor" stroke-width="1.8"/><path d="m16 16 5 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                <span class="visually-hidden">Search bookings</span>
                <input type="search" placeholder="Reference, client, destination or package" autocomplete="off" data-manager-search>
            </label>
        </div>

        <p class="admin-booking-manager-message" role="status" aria-live="polite" data-manager-message hidden></p>

        <div class="admin-booking-manager-body">
            <table class="admin-bookings-table admin-booking-manager-table">
                <thead><tr><th>Client</th><th>Destination</th><th>Travel date</th><th>Guests</th><th>Amount</th><th>Status</th><th><span class="visually-hidden">Actions</span></th></tr></thead>
                <tbody>
                    @foreach ($bookings as $booking)
                        <tr data-manager-row data-status="{{ $booking->status->value }}" data-search="{{ \Illuminate\Support\Str::lower(implode(' ', array_filter([$booking->reference, $booking->client->name, $booking->client->email, $booking->destination, $booking->package_name]))) }}">
                            <td>
                                <a class="admin-customer" href="{{ route('admin.clients.show', $booking->user_id) }}">
                                    <span class="admin-customer-avatar avatar-{{ $avatarTones[$booking->user_id % 4] }}" aria-hidden="true">{{ $booking->client->initials() }}</span>
                                    <span><strong>{{ $booking->client->name }}</strong><small>{{ $booking->reference }}</small></span>
                                </a>
                            </td>
                            <td>{{ $booking->destination }}@if ($booking->wasBookedOnline()) <span class="admin-online-badge">Online</span>@endif @if ($booking->package_name)<small class="admin-cell-note">{{ $booking->package_name }}</small>@endif</td>
                            <td>{{ $booking->travel_date->format('d M Y') }}@if ($booking->return_date)<small class="admin-cell-note">to {{ $booking->return_date->format('d M Y') }}</small>@endif</td>
                            <td>{{ $booking->guestsLabel() }}</td>
                            <td class="admin-amount">@if ($booking->amount === null)<span class="admin-cell-note">To be quoted</span>@else RM {{ number_format((float) $booking->amount, 2) }}@endif</td>
                            <td>
                                <label class="admin-status-select status-{{ $booking->status->value }}" data-manager-status-field>
                                    <span class="visually-hidden">Status of booking {{ $booking->reference }}</span>
                                    <select data-manager-status-select data-url="{{ route('admin.bookings.status', $booking) }}" data-reference="{{ $booking->reference }}">
                                        @foreach (\App\Enums\BookingStatus::cases() as $status)
                                            <option value="{{ $status->value }}" @selected($booking->status === $status)>{{ $status->label() }}</option>
                                        @endforeach
                                    </select>
                                </label>
                            </td>
                            <td class="admin-booking-manager-links">
                                <a class="admin-row-link" href="{{ route('admin.bookings.edit', $booking) }}">Edit</a>
                                <a class="admin-row-link" href="{{ route('admin.clients.show', $booking->user_id) }}">Client</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <p class="admin-table-empty" @if ($bookings->isNotEmpty()) hidden @endif data-manager-empty>{{ $bookings->isEmpty() ? 'No bookings yet. Create the first one with “New booking”.' : 'No bookings match these filters.' }}</p>
        </div>

        <footer class="admin-booking-manager-foot">
            <span data-manager-result>Showing {{ $bookings->count() }} of {{ number_format($total) }} bookings</span>
            <a href="{{ route('admin.bookings.index') }}">View all bookings <span aria-hidden="true">→</span></a>
        </footer>
    </div>
</dialog>
