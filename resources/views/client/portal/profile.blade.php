@extends('client.portal.layout')

@section('title', 'Profile & security')

@section('content')
    <section class="cp-welcome">
        <div>
            <span class="cp-eyebrow">MY ACCOUNT</span>
            <h1>Profile &amp; security</h1>
            <p>Keep your contact details up to date so our team can reach you about your trips.</p>
        </div>
    </section>

    <div class="cp-columns">
        <section class="cp-panel" aria-labelledby="details-title" data-tour="profile-details">
            <header><h2 id="details-title"><span class="cp-panel-icon">@include('client.portal.partials.icon', ['name' => 'user'])</span>Your details</h2></header>
            @if ($errors->default->any())
                <div class="cp-errors" role="alert">
                    <ul>
                        @foreach ($errors->default->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <form class="cp-form" method="POST" action="{{ route('client.profile.update') }}">
                @csrf
                @method('PUT')
                <label class="cp-field cp-field--wide"><span>Full name</span><input name="name" value="{{ old('name', $client->name) }}" required maxlength="120" autocomplete="name"></label>
                <label class="cp-field cp-field--wide"><span>Phone number</span><input type="tel" name="phone" value="{{ old('phone', $client->phone) }}" required maxlength="20" autocomplete="tel"></label>
                <label class="cp-field cp-field--wide">
                    <span>Email <small>Contact us to change the email you sign in with</small></span>
                    <input type="email" value="{{ $client->email }}" readonly aria-readonly="true">
                </label>
                <button class="cp-button" type="submit">Save details</button>
            </form>
        </section>

        <section class="cp-panel" aria-labelledby="password-title" data-tour="profile-password">
            <header><h2 id="password-title"><span class="cp-panel-icon">@include('client.portal.partials.icon', ['name' => 'shield'])</span>Change password</h2></header>
            @if ($errors->password->any())
                <div class="cp-errors" role="alert">
                    <ul>
                        @foreach ($errors->password->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <form class="cp-form" method="POST" action="{{ route('client.password.update') }}">
                @csrf
                @method('PUT')
                <label class="cp-field cp-field--wide"><span>Current password</span><input type="password" name="current_password" required autocomplete="current-password"></label>
                <label class="cp-field cp-field--wide"><span>New password <small>At least 8 characters</small></span><input type="password" name="password" required minlength="8" autocomplete="new-password"></label>
                <label class="cp-field cp-field--wide"><span>Confirm new password</span><input type="password" name="password_confirmation" required minlength="8" autocomplete="new-password"></label>
                <button class="cp-button" type="submit">Update password</button>
            </form>
        </section>
    </div>
@endsection
