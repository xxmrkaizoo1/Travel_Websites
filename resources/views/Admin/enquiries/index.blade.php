@extends('Admin.layouts.workspace')

@section('title', 'Enquiries')

@section('content')
    <section class="admin-welcome">
        <div>
            <span class="admin-eyebrow">WORKSPACE</span>
            <h1>Enquiries</h1>
            <p>Ticket enquiries from the website arrive here automatically. Log calls, WhatsApp chats and walk-ins too, then turn them into bookings.</p>
        </div>
        <a class="admin-primary-button" href="{{ route('admin.enquiries.create') }}"><span aria-hidden="true">＋</span> Log enquiry</a>
    </section>

    <section class="admin-panel admin-bookings-panel">
        <div class="admin-panel-heading admin-bookings-heading">
            <nav class="admin-filter-tabs" aria-label="Filter enquiries">
                @foreach (['pending' => 'Needs reply', 'closed' => 'Closed', 'all' => 'All'] as $tabKey => $tabLabel)
                    <a href="{{ route('admin.enquiries.index', array_filter(['tab' => $tabKey === 'pending' ? null : $tabKey, 'search' => $filters['search'] ?? null])) }}" @class(['is-active' => $tab === $tabKey])>{{ $tabLabel }} <span>{{ number_format($counts[$tabKey]) }}</span></a>
                @endforeach
            </nav>
            <form class="admin-search" method="GET" action="{{ route('admin.enquiries.index') }}" role="search">
                @if ($tab !== 'pending')
                    <input type="hidden" name="tab" value="{{ $tab }}">
                @endif
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="10.8" cy="10.8" r="6.8" stroke="currentColor" stroke-width="1.8"/><path d="m16 16 5 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Name, contact, destination or message" aria-label="Search enquiries">
            </form>
        </div>

        <div class="admin-enquiry-list">
            @forelse ($enquiries as $enquiry)
                <article class="admin-enquiry">
                    <header>
                        <div>
                            <strong>{{ $enquiry->name }}</strong>
                            @if ($enquiry->client)
                                <a class="admin-enquiry-client" href="{{ route('admin.clients.show', $enquiry->user_id) }}">Client account →</a>
                            @endif
                        </div>
                        <span class="admin-status status-{{ $enquiry->status->badge() }}">{{ $enquiry->status->label() }}</span>
                    </header>
                    <p class="admin-enquiry-meta">
                        <span>{{ $enquiry->source->label() }}</span>
                        <span title="{{ $enquiry->created_at->format('d M Y, g:i a') }}">{{ $enquiry->created_at->diffForHumans() }}</span>
                        @if ($enquiry->destination)
                            <span>✈ {{ $enquiry->destination }}</span>
                        @endif
                        @if ($enquiry->phone)
                            <a href="https://wa.me/{{ preg_replace('/\D/', '', preg_replace('/^0/', '60', $enquiry->phone)) }}" target="_blank" rel="noreferrer">{{ $enquiry->phone }}</a>
                        @endif
                        @if ($enquiry->email)
                            <a href="mailto:{{ $enquiry->email }}">{{ $enquiry->email }}</a>
                        @endif
                    </p>
                    <p class="admin-enquiry-message">{{ $enquiry->message }}</p>
                    <footer>
                        @if ($enquiry->booking)
                            <a class="admin-row-link" href="{{ route('admin.bookings.edit', $enquiry->booking_id) }}">Booked as {{ $enquiry->booking->reference }} →</a>
                        @else
                            <a class="admin-primary-button admin-button-small" href="{{ route('admin.bookings.create', ['enquiry' => $enquiry->id]) }}">Create booking</a>
                        @endif
                        @foreach (\App\Enums\EnquiryStatus::cases() as $status)
                            @continue($status === $enquiry->status)
                            <form method="POST" action="{{ route('admin.enquiries.update', $enquiry) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="{{ $status->value }}">
                                <button class="admin-secondary-button admin-button-small" type="submit">{{ $status->actionLabel() }}</button>
                            </form>
                        @endforeach
                        <form method="POST" action="{{ route('admin.enquiries.destroy', $enquiry) }}" data-confirm="Delete the enquiry from {{ $enquiry->name }}? This cannot be undone.">
                            @csrf
                            @method('DELETE')
                            <button class="admin-text-danger" type="submit">Delete</button>
                        </form>
                    </footer>
                </article>
            @empty
                <p class="admin-table-empty">{{ $tab === 'pending' && blank($filters['search'] ?? null) ? 'All caught up: no enquiries need a reply.' : 'No enquiries match these filters.' }}</p>
            @endforelse
        </div>

        <div class="admin-table-footer">
            <span>Showing <strong>{{ $enquiries->count() }}</strong> of {{ number_format($enquiries->total()) }} enquiries</span>
            {{ $enquiries->links('Admin.partials.pagination') }}
        </div>
    </section>
@endsection
