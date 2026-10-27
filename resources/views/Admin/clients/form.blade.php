@extends('Admin.layouts.workspace')

@php($breadcrumbParent = ['Clients', route('admin.clients.index')])

@section('title', 'Add client')

@section('content')
    <section class="admin-welcome">
        <div>
            <span class="admin-eyebrow">CLIENTS</span>
            <h1>Add a client account</h1>
            <p>For clients who book by phone, WhatsApp or walk-in. The account is ready to use straight away: give the client their email and temporary password so they can sign in on the website.</p>
        </div>
    </section>

    <form class="admin-panel admin-record-form" method="POST" action="{{ route('admin.clients.store') }}">
        @csrf
        @include('Admin.clients.partials.fields', ['client' => $client, 'passwordRequired' => true])

        <div class="admin-form-actions">
            <a class="admin-secondary-button" href="{{ route('admin.clients.index') }}">Cancel</a>
            <button class="admin-primary-button" type="submit">Create client account <span aria-hidden="true">→</span></button>
        </div>
    </form>
@endsection
