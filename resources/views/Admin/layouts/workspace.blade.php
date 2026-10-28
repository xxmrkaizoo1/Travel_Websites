<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f4f7fc">
    <meta name="description" content="Smart Holidays administration dashboard.">
    <title>@yield('title') | Smart Holidays Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('Admin.partials.theme-head')
    <style>@view-transition { navigation: auto; }</style>
</head>
<body class="admin-page">
    <a class="skip-link" href="#admin-main">Skip to content</a>

    <div class="admin-shell">
        @include('Admin.partials.sidebar')

        <div class="admin-main-wrap">
            <header class="admin-topbar">
                <button class="admin-menu-toggle" type="button" aria-expanded="false" aria-controls="admin-sidebar" aria-label="Open admin navigation">
                    <span></span><span></span><span></span>
                </button>
                <div class="admin-breadcrumb">
                    <a href="{{ route('admin.dashboard') }}">Workspace</a>
                    @foreach (array_filter([$breadcrumbParent ?? null]) as [$parentLabel, $parentUrl])
                        <span aria-hidden="true">/</span><a href="{{ $parentUrl }}">{{ $parentLabel }}</a>
                    @endforeach
                    <span aria-hidden="true">/</span><strong>@yield('title')</strong>
                </div>
                <div class="admin-topbar-actions">
                    <span class="admin-today"><span aria-hidden="true">◷</span> {{ now()->format('D, d M Y') }}</span>
                    <a class="admin-icon-button" href="{{ route('admin.enquiries.index') }}" data-admin-tour="notifications" aria-label="{{ ($sidebarPendingEnquiries ?? 0) > 0 ? $sidebarPendingEnquiries.' pending enquiries' : 'Enquiries' }}" title="Pending enquiries">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M18 9a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9ZM10 21h4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        @if (($sidebarPendingEnquiries ?? 0) > 0)
                            <span class="admin-notification-dot"></span>
                        @endif
                    </a>
                    <span class="admin-top-avatar" title="{{ auth()->user()->name }}">{{ auth()->user()->initials() }}</span>
                </div>
            </header>

            <main class="admin-content" id="admin-main">
                @if (session('status'))
                    <div class="homepage-editor-alert" role="status">{{ session('status') }}</div>
                @endif

                @if ($errors->any())
                    <div class="homepage-editor-errors" role="alert">
                        <strong>Please check the highlighted fields.</strong>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')

                <footer class="admin-footer">© {{ date('Y') }} Smart Holidays by Roystar Travel &amp; Tours</footer>
            </main>
        </div>
    </div>
</body>
</html>
