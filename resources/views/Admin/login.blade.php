<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f4f7fc">
    <meta name="description" content="Sign in to the Smart Holidays administration portal.">
    <title>Admin Sign In | Smart Holidays</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>@view-transition { navigation: auto; }</style>
</head>
<body class="admin-login-page">
    <a class="skip-link" href="#admin-login-main">Skip to sign in</a>

    <main class="admin-login-layout" id="admin-login-main">
        <section class="admin-login-story" aria-label="Smart Holidays">
            <a class="admin-login-brand" href="{{ route('home') }}">
                <span class="admin-brand-mark" aria-hidden="true">
                    <img src="{{ asset('images/smart-holidays-logo.png') }}" alt="">
                </span>
                <span><strong>SMART HOLIDAYS</strong><small>ROYSTAR TRAVEL &amp; TOURS</small></span>
            </a>

            <div class="admin-login-story-copy">
                <span class="admin-login-kicker">YOUR TRAVEL BUSINESS, IN ONE PLACE</span>
                <h1>Every journey<br>starts with a plan.</h1>
                <p>Sign in to manage bookings, explore destination insights and keep your team moving.</p>
            </div>

            <div class="admin-login-story-image" role="img" aria-label="Aerial view of a tropical island and turquoise sea"></div>
            <span class="admin-login-story-note"><span aria-hidden="true">✦</span> Made for journeys worth remembering</span>
        </section>

        <section class="admin-login-panel" aria-labelledby="admin-login-title">
            <a class="admin-login-back" href="{{ route('home') }}"><span aria-hidden="true">←</span> Back to website</a>

            <div class="admin-login-card">
                <span class="admin-login-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="M12 3 5 6v5c0 4.7 2.8 8 7 10 4.2-2 7-5.3 7-10V6l-7-3Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
                        <path d="m9 12 2 2 4-4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
                <span class="admin-login-kicker">ADMIN PORTAL</span>
                <h2 id="admin-login-title">Welcome back</h2>
                <p class="admin-login-intro">Sign in with your administrator account to continue.</p>

                @if ($errors->any())
                    <div class="admin-login-error" role="alert">
                        <span aria-hidden="true">!</span>
                        <p>{{ $errors->first() }}</p>
                    </div>
                @endif

                <form class="admin-login-form" method="POST" action="{{ route('login.store') }}">
                    @csrf
                    <label for="admin-email">Email address</label>
                    <input
                        id="admin-email"
                        name="email"
                        type="email"
                        value="{{ old('email') }}"
                        autocomplete="username"
                        placeholder="you@company.com"
                        required
                        autofocus
                    >

                    <div class="admin-password-label">
                        <label for="admin-password">Password</label>
                    </div>
                    <input
                        id="admin-password"
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

                    <button class="admin-login-submit" type="submit">Sign in to dashboard <span aria-hidden="true">→</span></button>
                </form>

                <p class="admin-login-support">Need access? <a href="mailto:tour@roystartravel.com.my">Contact your administrator</a></p>
            </div>

            <footer class="admin-login-footer">© {{ date('Y') }} Smart Holidays by Roystar Travel &amp; Tours</footer>
        </section>
    </main>
</body>
</html>
