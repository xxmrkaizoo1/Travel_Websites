<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f4f7fc">
    <meta name="description" content="Verify your email address to finish creating your Smart Holidays client account.">
    <title>Verify Your Email | Smart Holidays</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>@view-transition { navigation: auto; }</style>
</head>
<body class="admin-login-page client-auth-page">
    <a class="skip-link" href="#client-verify-main">Skip to email verification</a>

    <main class="admin-login-layout" id="client-verify-main">
        <section class="admin-login-story" aria-label="Smart Holidays">
            <a class="admin-login-brand" href="{{ route('home') }}">
                <span class="admin-brand-mark" aria-hidden="true">
                    <img src="{{ asset('images/smart-holidays-logo.png') }}" alt="">
                </span>
                <span><strong>SMART HOLIDAYS</strong><small>ROYSTAR TRAVEL &amp; TOURS</small></span>
            </a>

            <div class="admin-login-story-copy">
                <span class="admin-login-kicker">ONE QUICK STEP</span>
                <h1>Confirm your<br>email address.</h1>
                <p>Enter the one-time code we sent to your inbox to complete your client account.</p>
            </div>

            <div class="admin-login-story-image" role="img" aria-label="Aerial view of a tropical island and turquoise sea"></div>
            <span class="admin-login-story-note"><span aria-hidden="true">✦</span> Made for journeys worth remembering</span>
        </section>

        <section class="admin-login-panel" aria-labelledby="client-verify-title">
            <a class="admin-login-back" href="{{ route('client.register') }}"><span aria-hidden="true">←</span> Back to registration</a>

            <div class="admin-login-card">
                <span class="admin-login-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none">
                        <rect x="3" y="5" width="18" height="14" rx="2" stroke="currentColor" stroke-width="1.7"/>
                        <path d="m4 7 8 6 8-6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
                <span class="admin-login-kicker">EMAIL VERIFICATION</span>
                <h2 id="client-verify-title">Check your email</h2>
                <p class="admin-login-intro">We sent a 6-digit code to <strong>{{ $email }}</strong>. The code expires in 10 minutes.</p>

                @if (session('status'))
                    <p class="client-auth-status" role="status">{{ session('status') }}</p>
                @endif

                @if ($errors->any())
                    <div class="admin-login-error" role="alert">
                        <span aria-hidden="true">!</span>
                        <p>{{ $errors->first() }}</p>
                    </div>
                @endif

                <form class="admin-login-form" method="POST" action="{{ route('client.verify.store') }}">
                    @csrf
                    <label for="client-verification-code">Verification code</label>
                    <input
                        id="client-verification-code"
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
                    <button class="admin-login-submit" type="submit">Verify email <span aria-hidden="true">→</span></button>
                </form>

                <form class="client-otp-resend-form" method="POST" action="{{ route('client.verify.resend') }}">
                    @csrf
                    <span>Didn’t receive the code?</span>
                    <button class="client-otp-resend" type="submit">Send a new code</button>
                </form>

                <p class="admin-login-support">Already verified? <a href="{{ route('client.login') }}">Sign in</a></p>
            </div>

            <footer class="admin-login-footer">© {{ date('Y') }} Smart Holidays by Roystar Travel &amp; Tours</footer>
        </section>
    </main>
    @include('partials.language-switcher')
</body>
</html>
