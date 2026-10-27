@extends('Admin.layouts.workspace')

@section('title', 'Bookings')

@section('content')
    <section class="admin-welcome">
        <div>
            <span class="admin-eyebrow">WORKSPACE</span>
            <h1>Bookings</h1>
            <p>Every booking made for your clients, newest first.</p>
        </div>
        <a class="admin-primary-button" href="{{ route('admin.bookings.create') }}"><span aria-hidden="true">＋</span> New booking</a>
    </section>

    <section class="admin-panel admin-bookings-panel">
        <div class="admin-panel-heading admin-bookings-heading">
            <nav class="admin-filter-tabs" aria-label="Filter bookings by status">
                <a href="{{ route('admin.bookings.index', array_filter(['search' => $filters['search'] ?? null])) }}" @class(['is-active' => empty($filters['status'])])>All <span>{{ number_format($statusCounts->sum()) }}</span></a>
                @foreach (\App\Enums\BookingStatus::cases() as $status)
                    <a href="{{ route('admin.bookings.index', array_filter(['status' => $status->value, 'search' => $filters['search'] ?? null])) }}" @class(['is-active' => ($filters['status'] ?? null) === $status->value])>{{ $status->label() }} <span>{{ number_format($statusCounts[$status->value] ?? 0) }}</span></a>
                @endforeach
            </nav>
            <form class="admin-search" method="GET" action="{{ route('admin.bookings.index') }}" role="search">
                @if (! empty($filters['status']))
                    <input type="hidden" name="status" value="{{ $filters['status'] }}">
                @endif
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="10.8" cy="10.8" r="6.8" stroke="currentColor" stroke-width="1.8"/><path d="m16 16 5 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Reference, client or destination" aria-label="Search bookings">
            </form>
        </div>

        @include('Admin.bookings.partials.table', ['bookings' => $bookings, 'emptyMessage' => filled($filters['search'] ?? null) || filled($filters['status'] ?? null) ? 'No bookings match these filters.' : 'No bookings yet. Create the first one with “New booking”.'])

        <div class="admin-table-footer">
            <span>Showing <strong>{{ $bookings->count() }}</strong> of {{ number_format($bookings->total()) }} bookings</span>
            {{ $bookings->links('Admin.partials.pagination') }}
        </div>
    </section>
@endsection
