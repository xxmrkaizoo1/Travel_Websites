@extends('Admin.layouts.workspace')

@section('title', 'Destinations')

@section('content')
    <section class="admin-welcome">
        <div>
            <span class="admin-eyebrow">WORKSPACE</span>
            <h1>Destinations</h1>
            <p>Where your clients are travelling, ranked by bookings. Cancelled bookings are left out; revenue counts confirmed and completed bookings.</p>
        </div>
    </section>

    <section class="admin-panel admin-bookings-panel">
        <div class="admin-table-wrap">
            <table class="admin-bookings-table">
                <thead><tr><th>#</th><th>Destination</th><th>Bookings</th><th>Travellers</th><th>Revenue</th><th><span class="visually-hidden">Actions</span></th></tr></thead>
                <tbody>
                    @forelse ($destinations as $destination)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <span class="admin-customer">
                                    @if ($destination['image'])
                                        <img class="admin-destination-thumb" src="{{ $destination['image'] }}" alt="" loading="lazy">
                                    @else
                                        <span class="admin-destination-tile" aria-hidden="true">{{ $destination['initials'] }}</span>
                                    @endif
                                    <strong>{{ $destination['name'] }}</strong>
                                </span>
                            </td>
                            <td>{{ number_format($destination['bookings']) }}</td>
                            <td>{{ number_format($destination['travellers']) }}</td>
                            <td class="admin-amount">RM {{ number_format($destination['revenue'], 2) }}</td>
                            <td><a class="admin-row-link" href="{{ route('admin.bookings.index', ['search' => $destination['name']]) }}">View bookings <span aria-hidden="true">→</span></a></td>
                        </tr>
                    @empty
                        <tr><td class="admin-table-empty" colspan="6">No bookings yet, so there are no destinations to rank.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
