@extends('Admin.layouts.workspace')

@section('title', 'Clients')

@section('content')
    <section class="admin-welcome">
        <div>
            <span class="admin-eyebrow">MANAGEMENT</span>
            <h1>Clients</h1>
            <p>Client accounts from website sign-ups and the ones your team adds. Open a client to edit their details, reset their password or suspend the account.</p>
        </div>
        <a class="admin-primary-button" href="{{ route('admin.clients.create') }}"><span aria-hidden="true">＋</span> Add client</a>
    </section>

    @php($avatarTones = ['sky', 'lilac', 'peach', 'mint'])
    <section class="admin-panel admin-bookings-panel">
        <div class="admin-panel-heading admin-bookings-heading">
            <nav class="admin-filter-tabs" aria-label="Filter clients">
                @foreach (['all' => 'All', 'active' => 'Active', 'suspended' => 'Suspended', 'unverified' => 'Email not verified'] as $filterKey => $filterLabel)
                    <a href="{{ route('admin.clients.index', array_filter(['filter' => $filterKey === 'all' ? null : $filterKey, 'search' => $filters['search'] ?? null])) }}" @class(['is-active' => ($filters['filter'] ?? 'all') === $filterKey])>{{ $filterLabel }} <span>{{ number_format($counts[$filterKey]) }}</span></a>
                @endforeach
            </nav>
            <form class="admin-search" method="GET" action="{{ route('admin.clients.index') }}" role="search">
                @if (! empty($filters['filter']))
                    <input type="hidden" name="filter" value="{{ $filters['filter'] }}">
                @endif
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="10.8" cy="10.8" r="6.8" stroke="currentColor" stroke-width="1.8"/><path d="m16 16 5 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Name, email or phone" aria-label="Search clients">
            </form>
        </div>

        <div class="admin-table-wrap">
            <table class="admin-bookings-table">
                <thead><tr><th>Client</th><th>Phone</th><th>Bookings</th><th>Total spent</th><th>Joined</th><th>Account</th><th><span class="visually-hidden">Actions</span></th></tr></thead>
                <tbody>
                    @forelse ($clients as $client)
                        <tr>
                            <td>
                                <a class="admin-customer" href="{{ route('admin.clients.show', $client) }}">
                                    <span class="admin-customer-avatar avatar-{{ $avatarTones[$client->id % 4] }}" aria-hidden="true">{{ $client->initials() }}</span>
                                    <span><strong>{{ $client->name }}</strong><small>{{ $client->email }}</small></span>
                                </a>
                            </td>
                            <td>{{ $client->phone ?: '—' }}</td>
                            <td>{{ number_format($client->bookings_count) }}</td>
                            <td class="admin-amount">RM {{ number_format((float) $client->revenue, 2) }}</td>
                            <td>{{ $client->created_at->format('d M Y') }}</td>
                            <td>@include('Admin.clients.partials.account-status', ['client' => $client])</td>
                            <td><a class="admin-row-link" href="{{ route('admin.clients.show', $client) }}">Manage <span aria-hidden="true">→</span></a></td>
                        </tr>
                    @empty
                        <tr><td class="admin-table-empty" colspan="7">{{ filled($filters['search'] ?? null) || filled($filters['filter'] ?? null) ? 'No clients match these filters.' : 'No client accounts yet. Clients appear here when they sign up on the website, or add one with “Add client”.' }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="admin-table-footer">
            <span>Showing <strong>{{ $clients->count() }}</strong> of {{ number_format($clients->total()) }} clients</span>
            {{ $clients->links('Admin.partials.pagination') }}
        </div>
    </section>
@endsection
