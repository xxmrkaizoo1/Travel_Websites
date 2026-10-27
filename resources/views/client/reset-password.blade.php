<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f4f7fc">
    <meta name="description" content="Enter your code and choose a new password for your Smart Holidays client account.">
    <title>Choose a New Password | Smart Holidays</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>@view-transition { navigation: auto; }</style>
</head>
<body class="admin-login-page client-auth-page">
    <a class="skip-link" href="#client-reset-main">Skip to new password form</a>

    <main class="admin-login-layout" id="client-reset-main">
        <section class="admin-login-story" aria-label="Smart Holidays">
            <a class="admin-login-brand" href="{{ route('home') }}">
                <span class="admin-brand-mark" aria-hidden="true">
                    <img src="{{ asset('images/smart-holidays-logo.png') }}" alt="">
                </span>
                <span><strong>SMART HOLIDAYS</strong><small>ROYSTAR TRAVEL &amp; TOURS</small></span>
            </a>

            <div class="admin-login-story-copy">
                <span class="admin-login-kicker">ALMOST THERE</span>
                <h1>Choose a<br>new password.</h1>
                <p>Enter the code from your email, then pick a password you have not used here before.</p>
            </div>

            <div class="admin-login-story-image" role="img" aria-label="Aerial view of a tropical island and turquoise sea"></div>
            <span class="admin-login-story-note"><span aria-hidden="true">✦</span> Made for journeys worth remembering</span>
        </section>

        <section class="admin-login-panel" aria-labelledby="client-reset-title">
            <a class="admin-login-back" href="{{ route('client.password.request') }}"><span aria-hidden="true">←</span> Use a different email</a>

            <div class="admin-login-card">
                <span class="admin-login-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="M12 3 4.5 6v5.5c0 4.6 3.2 8.4 7.5 9.5 4.3-1.1 7.5-4.9 7.5-9.5V6z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
                        <path d="m9 12 2 2 4-4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
                <span class="admin-login-kicker">PASSWORD RESET</span>
                <h2 id="client-reset-title">Check your email</h2>
                <p class="admin-login-intro">If <strong>{{ $email }}</strong> has a client account, we sent it a 6-digit code. The code expires in 10 minutes.</p>

                @if (session('status'))
                    <p class="client-auth-status" role="status">{{ session('status') }}</p>
                @endif

                @if ($errors->any())
                    <div class="admin-login-error" role="alert">
                        <span aria-hidden="true">!</span>
                        <p>{{ $errors->first() }}</p>
                    </div>
                @endif

                <form class="admin-login-form" method="POST" action="{{ route('client.password.store') }}">
                    @csrf
                    <label for="client-reset-code">Reset code</label>
                    <input
                        id="client-reset-code"
                        class="client-otp-input"
                        name="code"
                        type="text"
                        inputmode="numeric"
                        autocomplete="one-time-code"
                        pattern="[0-9]{6}"
                        minlength="6"
                        maxlength="6"
                        placeholder="000000"
                        required
                        autofocus
                    >

                    <div class="admin-password-label">
                        <label for="client-new-password">New password</label>
                    </div>
                    <input
                        id="client-new-password"
                        name="password"
                        type="password"
                        autocomplete="new-password"
                        minlength="8"
                        placeholder="At least 8 characters"
                        required
                    >

                    <div class="admin-password-label">
                        <label for="client-new-password-confirmation">Confirm new password</label>
                    </div>
                    <input
                        id="client-new-password-confirmation"
                        name="password_confirmation"
                        type="password"
                        autocomplete="new-password"
                        minlength="8"
                        placeholder="Type it again"
                        required
                    >

                    <button class="admin-login-submit" type="submit">Reset password <span aria-hidden="true">→</span></button>
                </form>

                <form class="client-otp-resend-form" method="POST" action="{{ route('client.password.resend') }}">
                    @csrf
                    <span>Didn’t receive the code?</span>
                    <button class="client-otp-resend" type="submit">Send a new code</button>
                </form>

                <p class="admin-login-support">Remembered it? <a href="{{ route('client.login') }}">Sign in</a></p>
            </div>

            <footer class="admin-login-footer">© {{ date('Y') }} Smart Holidays by Roystar Travel &amp; Tours</footer>
        </section>
    </main>
    @include('partials.language-switcher')
</body>
</html>
