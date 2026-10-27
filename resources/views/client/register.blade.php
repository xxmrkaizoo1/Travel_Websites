<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f4f7fc">
    <meta name="description" content="Create a Smart Holidays client account.">
    <title>Create Client Account | Smart Holidays</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>@view-transition { navigation: auto; }</style>
</head>
<body class="admin-login-page client-auth-page">
    <a class="skip-link" href="#client-register-main">Skip to account creation</a>

    <main class="admin-login-layout" id="client-register-main">
        <section class="admin-login-story" aria-label="Smart Holidays">
            <a class="admin-login-brand" href="{{ route('home') }}">
                <span class="admin-brand-mark" aria-hidden="true">
                    <img src="{{ asset('images/smart-holidays-logo.png') }}" alt="">
                </span>
                <span><strong>SMART HOLIDAYS</strong><small>ROYSTAR TRAVEL &amp; TOURS</small></span>
            </a>

            <div class="admin-login-story-copy">
                <span class="admin-login-kicker">LET'S PLAN SOMETHING MEMORABLE</span>
                <h1>Your next<br>adventure awaits.</h1>
                <p>Create a client account to stay connected with your Smart Holidays travel plans.</p>
            </div>

            <div class="admin-login-story-image" role="img" aria-label="Aerial view of a tropical island and turquoise sea"></div>
            <span class="admin-login-story-note"><span aria-hidden="true">✦</span> Made for journeys worth remembering</span>
        </section>

        <section class="admin-login-panel" aria-labelledby="client-register-title">
            <a class="admin-login-back" href="{{ route('home') }}"><span aria-hidden="true">←</span> Back to website</a>

            <div class="admin-login-card">
                <span class="admin-login-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="M16 20v-1.5a4.5 4.5 0 0 0-4.5-4.5h-4A4.5 4.5 0 0 0 3 18.5V20M9.5 10a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7ZM17 8h4m-2-2v4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
                <span class="admin-login-kicker">CLIENT ACCOUNT</span>
                <h2 id="client-register-title">Create your account</h2>
                <p class="admin-login-intro">Join Smart Holidays and verify your email to finish creating your client account.</p>

                @if ($errors->any())
                    <div class="admin-login-error" role="alert">
                        <span aria-hidden="true">!</span>
                        <p>{{ $errors->first() }}</p>
                    </div>
                @endif

                <form class="admin-login-form" method="POST" action="{{ route('client.register.store') }}">
                    @csrf
                    <label for="client-name">Full name</label>
                    <input
                        id="client-name"
                        name="name"
                        type="text"
                        value="{{ old('name') }}"
                        autocomplete="name"
                        placeholder="Your name"
                        maxlength="120"
                        required
                        autofocus
                    >

                    <label for="client-email">Email address</label>
                    <input
                        id="client-email"
                        name="email"
                        type="email"
                        value="{{ old('email') }}"
                        autocomplete="email"
                        placeholder="you@example.com"
                        required
                    >

                    <label for="client-phone">Phone number</label>
                    <div class="client-phone-row">
                        <select id="client-phone-country" name="phone_country" aria-label="Country calling code" autocomplete="country">
                            @php($selectedCountry = old('phone_country', config('phone_countries.default')))
                            @foreach (config('phone_countries.countries') as $iso => [$countryName, $callingCode])
                                <option value="{{ $iso }}" @selected($selectedCountry === $iso)>{{ $countryName }} (+{{ $callingCode }})</option>
                            @endforeach
                        </select>
                        <input
                            id="client-phone"
                            name="phone"
                            type="tel"
                            inputmode="tel"
                            value="{{ old('phone') }}"
                            autocomplete="tel-national"
                            placeholder="123456789"
                            maxlength="20"
                            required
                        >
                    </div>

                    <label for="client-password">Password</label>
                    <input
                        id="client-password"
                        name="password"
                        type="password"
                        autocomplete="new-password"
                        placeholder="At least 8 characters"
                        required
                    >

                    <label for="client-password-confirmation">Confirm password</label>
                    <input
                        id="client-password-confirmation"
                        name="password_confirmation"
                        type="password"
                        autocomplete="new-password"
                        placeholder="Enter your password again"
                        required
                    >

                    <button class="admin-login-submit" type="submit">Create account <span aria-hidden="true">→</span></button>
                </form>

                <p class="admin-login-support">Already have an account? <a href="{{ route('client.login') }}">Sign in</a></p>
            </div>

            <footer class="admin-login-footer">© {{ date('Y') }} Smart Holidays by Roystar Travel &amp; Tours</footer>
        </section>
    </main>
    @include('partials.language-switcher')
</body>
</html>
