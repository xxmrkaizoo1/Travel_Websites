<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#ffffff">
    <meta name="robots" content="noindex">
    <title>@yield('title') | My Account · Smart Holidays</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{-- Animate moving between My Account pages; both pages must opt in, so this stays on portal pages only. --}}
    <style>@view-transition { navigation: auto; }</style>
</head>
<body class="client-portal-page">
    <a class="skip-link" href="#main">Skip to content</a>

    @include('partials.site-header')

    <div class="cp-shell">
        <aside class="cp-sidebar" aria-label="My account">
            <div class="cp-identity">
                <span class="cp-avatar" aria-hidden="true">{{ auth()->user()->initials() }}</span>
                <div>
                    <strong>{{ auth()->user()->name }}</strong>
                    <small>Member since {{ auth()->user()->created_at->format('M Y') }}</small>
                </div>
            </div>
            <nav class="cp-nav" aria-label="My account pages" data-tour="menu">
                @foreach ([
                    ['client.dashboard', 'client.dashboard', 'home', 'Overview', null],
                    ['client.bookings.index', 'client.bookings.*', 'plane', 'My bookings', $portalCounts['bookings']],
                    ['client.enquiries.index', 'client.enquiries.*', 'chat', 'Trip requests', $portalCounts['requests']],
                    ['client.profile.edit', 'client.profile.*', 'user', 'Profile & security', null],
                ] as [$routeName, $activePattern, $icon, $label, $count])
                    <a @class(['is-active' => request()->routeIs($activePattern)]) href="{{ route($routeName) }}" @if (request()->routeIs($activePattern)) aria-current="page" @endif>
                        <span class="cp-nav-icon">@include('client.portal.partials.icon', ['name' => $icon])</span>
                        <span class="cp-nav-label">{{ $label }}</span>
                        @if ($count)
                            <span class="cp-nav-count" aria-label="{{ $count }} open">{{ $count }}</span>
                        @endif
                    </a>
                @endforeach
            </nav>
            <a class="cp-sidebar-card cp-sidebar-card--explore" href="{{ route('smart-muslim') }}" data-tour="explore">
                <span class="cp-sidebar-card-icon">@include('client.portal.partials.icon', ['name' => 'compass'])</span>
                <strong>Find your next trip</strong>
                <span>Browse Smart Muslim packages and book online.</span>
            </a>
            <a class="cp-sidebar-card" href="https://wa.me/60165500590" target="_blank" rel="noreferrer" data-tour="help">
                <span class="cp-sidebar-card-icon cp-sidebar-card-icon--green">@include('client.portal.partials.icon', ['name' => 'whatsapp'])</span>
                <strong>Need help with a trip?</strong>
                <span>WhatsApp our team ↗</span>
            </a>
            <button class="cp-tour-replay" type="button" data-tour-start>@include('client.portal.partials.icon', ['name' => 'compass']) Take the tour</button>
            <form method="POST" action="{{ route('client.logout') }}">
                @csrf
                <button class="cp-signout" type="submit">@include('client.portal.partials.icon', ['name' => 'logout']) Sign out</button>
            </form>
        </aside>

        <main class="cp-main" id="main">
            @if (session('status'))
                <div class="cp-alert" role="status">{{ session('status') }}</div>
            @endif

            @yield('content')
        </main>
    </div>

    <footer class="site-footer">
        <div class="footer-bottom">
            © {{ date('Y') }} Smart Holidays by Roystar Travel &amp; Tours · <a href="{{ route('privacy-policy') }}">Privacy Policy</a> · <a href="{{ route('contact') }}">Contact Us</a>
        </div>
    </footer>
</body>
</html>
