<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f4f7fc">
    <meta name="description" content="Sign in to your Smart Holidays client account.">
    <title>Client Sign In | Smart Holidays</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="admin-login-page client-auth-page">
    <a class="skip-link" href="#client-login-main">Skip to sign in</a>

    <main class="admin-login-layout" id="client-login-main">
        <section class="admin-login-story" aria-label="Smart Holidays">
            <a class="admin-login-brand" href="{{ route('home') }}">
                <span class="admin-brand-mark" aria-hidden="true">
                    <img src="{{ asset('images/smart-holidays-logo.png') }}" alt="">
                </span>
                <span><strong>SMART HOLIDAYS</strong><small>ROYSTAR TRAVEL &amp; TOURS</small></span>
            </a>

            <div class="admin-login-story-copy">
                <span class="admin-login-kicker">YOUR NEXT JOURNEY STARTS HERE</span>
                <h1>Travel plans,<br>all in one place.</h1>
                <p>Sign in to your client account to keep connected with Smart Holidays.</p>
            </div>

            <div class="admin-login-story-image" role="img" aria-label="Aerial view of a tropical island and turquoise sea"></div>
            <span class="admin-login-story-note"><span aria-hidden="true">✦</span> Made for journeys worth remembering</span>
        </section>

        <section class="admin-login-panel" aria-labelledby="client-login-title">
            <a class="admin-login-back" href="{{ route('home') }}"><span aria-hidden="true">←</span> Back to website</a>

            <div class="admin-login-card">
                <span class="admin-login-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="M16 20v-1.5a4.5 4.5 0 0 0-4.5-4.5h-4A4.5 4.5 0 0 0 3 18.5V20M9.5 10a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7ZM17 8h4m-2-2v4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
                <span class="admin-login-kicker">CLIENT ACCOUNT</span>
                <h2 id="client-login-title">Welcome back</h2>
                <p class="admin-login-intro">Sign in to continue with your travel plans.</p>

                @if ($errors->any())
                    <div class="admin-login-error" role="alert">
                        <span aria-hidden="true">!</span>
                        <p>{{ $errors->first() }}</p>
                    </div>
                @endif

                <form class="admin-login-form" method="POST" action="{{ route('client.login.store') }}">
                    @csrf
                    <label for="client-email">Email address</label>
                    <input
                        id="client-email"
                        name="email"
                        type="email"
                        value="{{ old('email') }}"
                        autocomplete="username"
                        placeholder="you@example.com"
                        required
                        autofocus
                    >

                    <div class="admin-password-label">
                        <label for="client-password">Password</label>
                    </div>
                    <input
                        id="client-password"
                        name="password"
                        type="password"
                        autocomplete="current-password"
                        placeholder="Enter your password"
                        required
                    >

                    <label class="admin-remember">
                        <input type="checkbox" name="remember" value="1" @checked(old('remember'))>
                        <span>Keep me signed in</span>
                    </label>

                    <button class="admin-login-submit" type="submit">Sign in <span aria-hidden="true">→</span></button>
                </form>

                <p class="admin-login-support">New to Smart Holidays? <a href="{{ route('client.register') }}">Create a client account</a></p>
            </div>

            <footer class="admin-login-footer">© {{ date('Y') }} Smart Holidays by Roystar Travel &amp; Tours</footer>
        </section>
    </main>
</body>
</html>
