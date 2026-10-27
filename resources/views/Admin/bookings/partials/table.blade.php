{{-- Bookings table with row actions. Expects $bookings (with client loaded) and $emptyMessage. --}}
@php($avatarTones = ['sky', 'lilac', 'peach', 'mint'])
<div class="admin-table-wrap">
    <table class="admin-bookings-table">
        <thead><tr><th>Client</th><th>Destination</th><th>Travel date</th><th>Guests</th><th>Amount</th><th>Status</th><th><span class="visually-hidden">Actions</span></th></tr></thead>
        <tbody>
            @forelse ($bookings as $booking)
                <tr>
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
                    <td><span class="admin-status status-{{ $booking->status->value }}">{{ $booking->status->label() }}</span></td>
                    <td>
                        <details class="admin-row-menu">
                            <summary class="admin-row-action" aria-label="Actions for booking {{ $booking->reference }}">···</summary>
                            <div class="admin-row-menu-list">
                                <a href="{{ route('admin.bookings.edit', $booking) }}">Edit booking</a>
                                <a href="{{ route('admin.clients.show', $booking->user_id) }}">View client</a>
                                <form method="POST" action="{{ route('admin.bookings.destroy', $booking) }}" data-confirm="Delete booking {{ $booking->reference }}? This cannot be undone.">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit">Delete booking</button>
                                </form>
                            </div>
                        </details>
                    </td>
                </tr>
            @empty
                <tr><td class="admin-table-empty" colspan="7">{{ $emptyMessage }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
