<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f4f7fc">
    <meta name="description" content="Reset the password for your Smart Holidays client account.">
    <title>Forgot Password | Smart Holidays</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>@view-transition { navigation: auto; }</style>
</head>
<body class="admin-login-page client-auth-page">
    <a class="skip-link" href="#client-forgot-main">Skip to password reset</a>

    <main class="admin-login-layout" id="client-forgot-main">
        <section class="admin-login-story" aria-label="Smart Holidays">
            <a class="admin-login-brand" href="{{ route('home') }}">
                <span class="admin-brand-mark" aria-hidden="true">
                    <img src="{{ asset('images/smart-holidays-logo.png') }}" alt="">
                </span>
                <span><strong>SMART HOLIDAYS</strong><small>ROYSTAR TRAVEL &amp; TOURS</small></span>
            </a>

            <div class="admin-login-story-copy">
                <span class="admin-login-kicker">IT HAPPENS TO EVERYONE</span>
                <h1>Let’s get you<br>back on board.</h1>
                <p>We will email you a one-time code so you can choose a new password in a minute.</p>
            </div>

            <div class="admin-login-story-image" role="img" aria-label="Aerial view of a tropical island and turquoise sea"></div>
            <span class="admin-login-story-note"><span aria-hidden="true">✦</span> Made for journeys worth remembering</span>
        </section>

        <section class="admin-login-panel" aria-labelledby="client-forgot-title">
            <a class="admin-login-back" href="{{ route('client.login') }}"><span aria-hidden="true">←</span> Back to sign in</a>

            <div class="admin-login-card">
                <span class="admin-login-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none">
                        <rect x="4.5" y="10.5" width="15" height="10" rx="2" stroke="currentColor" stroke-width="1.7"/>
                        <path d="M8 10.5V7.5a4 4 0 0 1 8 0v3M12 14.5v2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                    </svg>
                </span>
                <span class="admin-login-kicker">FORGOT PASSWORD</span>
                <h2 id="client-forgot-title">Reset your password</h2>
                <p class="admin-login-intro">Enter the email you use to sign in. We will send you a 6-digit code to confirm it’s you.</p>

                @if ($errors->any())
                    <div class="admin-login-error" role="alert">
                        <span aria-hidden="true">!</span>
                        <p>{{ $errors->first() }}</p>
                    </div>
                @endif

                <form class="admin-login-form" method="POST" action="{{ route('client.password.email') }}">
                    @csrf
                    <label for="client-reset-email">Email address</label>
                    <input
                        id="client-reset-email"
                        name="email"
                        type="email"
                        value="{{ old('email') }}"
                        autocomplete="email"
                        placeholder="you@example.com"
                        required
                        autofocus
                    >
                    <button class="admin-login-submit" type="submit">Send reset code <span aria-hidden="true">→</span></button>
                </form>

                <p class="admin-login-support">Remembered it? <a href="{{ route('client.login') }}">Sign in</a></p>
            </div>

            <footer class="admin-login-footer">© {{ date('Y') }} Smart Holidays by Roystar Travel &amp; Tours</footer>
        </section>
    </main>
    @include('partials.language-switcher')
</body>
</html>
