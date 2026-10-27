@extends('Admin.layouts.workspace')

@php($breadcrumbParent = ['Clients', route('admin.clients.index')])

@section('title', $client->name)

@section('content')
    <section class="admin-client-hero admin-panel">
        <span class="admin-client-avatar" aria-hidden="true">{{ $client->initials() }}</span>
        <div class="admin-client-identity">
            <h1>{{ $client->name }}</h1>
            <p>
                <a href="mailto:{{ $client->email }}">{{ $client->email }}</a>
                @if ($client->phone)
                    · <a href="tel:{{ preg_replace('/[^0-9+]/', '', $client->phone) }}">{{ $client->phone }}</a>
                @endif
            </p>
            <div class="admin-client-tags">
                @include('Admin.clients.partials.account-status', ['client' => $client])
                <span>Joined {{ $client->created_at->format('d M Y') }}</span>
                @if ($client->isSuspended())
                    <span>Suspended {{ $client->suspended_at->format('d M Y') }}</span>
                @endif
            </div>
        </div>
        <dl class="admin-client-figures">
            <div><dt>Bookings</dt><dd>{{ number_format($client->bookings_count) }}</dd></div>
            <div><dt>Total spent</dt><dd>RM {{ number_format((float) $client->revenue, 2) }}</dd></div>
        </dl>
        <div class="admin-client-actions">
            <a class="admin-primary-button" href="{{ route('admin.bookings.create', ['client' => $client->id]) }}"><span aria-hidden="true">＋</span> New booking</a>
            @if ($client->isSuspended())
                <form method="POST" action="{{ route('admin.clients.suspension.destroy', $client) }}">
                    @csrf
                    @method('DELETE')
                    <button class="admin-secondary-button" type="submit">Reactivate account</button>
                </form>
            @else
                <form method="POST" action="{{ route('admin.clients.suspension.store', $client) }}" data-confirm="Suspend {{ $client->name }}? They will be signed out and cannot sign in until you reactivate the account.">
                    @csrf
                    <button class="admin-secondary-button" type="submit">Suspend account</button>
                </form>
            @endif
        </div>
    </section>

    <section class="admin-panel admin-bookings-panel">
        <div class="admin-panel-heading">
            <div><h2>Bookings</h2><p>Every trip booked for {{ $client->name }}, latest travel date first</p></div>
        </div>
        @include('Admin.bookings.partials.table', ['bookings' => $bookings, 'emptyMessage' => 'No bookings for this client yet.'])
    </section>

    @if ($enquiries->isNotEmpty())
        <section class="admin-panel">
            <div class="admin-panel-heading">
                <div><h2>Enquiries</h2><p>Latest messages from this client</p></div>
                <a class="admin-row-link" href="{{ route('admin.enquiries.index', ['tab' => 'all', 'search' => $client->email]) }}">View all <span aria-hidden="true">→</span></a>
            </div>
            <ul class="admin-mini-list">
                @foreach ($enquiries as $enquiry)
                    <li>
                        <span class="admin-status status-{{ $enquiry->status->badge() }}">{{ $enquiry->status->label() }}</span>
                        <div><strong>{{ $enquiry->destination ?: 'General enquiry' }}</strong><p>{{ \Illuminate\Support\Str::limit($enquiry->message, 140) }}</p></div>
                        <small>{{ $enquiry->created_at->format('d M Y') }}</small>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    <form class="admin-panel admin-record-form" method="POST" action="{{ route('admin.clients.update', $client) }}">
        @csrf
        @method('PUT')
        <div class="admin-panel-heading"><div><h2>Account details</h2><p>Changing the email updates the address the client signs in with.</p></div></div>
        @include('Admin.clients.partials.fields', ['client' => $client, 'passwordRequired' => false])
        <div class="admin-form-actions">
            <button class="admin-primary-button" type="submit">Save changes <span aria-hidden="true">→</span></button>
        </div>
    </form>

    <form class="admin-danger-zone" method="POST" action="{{ route('admin.clients.destroy', $client) }}" data-confirm="Delete {{ $client->name }}'s account permanently? This cannot be undone.">
        @csrf
        @method('DELETE')
        <div>
            <strong>Delete this client account</strong>
            <p>{{ $client->bookings_count > 0 ? 'Accounts with bookings cannot be deleted, so the booking history stays complete. Suspend the account instead.' : 'Permanently removes the account. Their enquiries stay in the inbox without a linked account.' }}</p>
        </div>
        <button class="admin-danger-button" type="submit" @disabled($client->bookings_count > 0)>Delete account</button>
    </form>
@endsection
