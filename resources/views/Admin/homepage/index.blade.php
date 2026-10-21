<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f4f7fc">
    <meta name="description" content="Manage the featured campaigns on the Smart Muslim homepage.">
    <title>Smart Muslim Homepage Content | Smart Holidays Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="admin-page">
    <a class="skip-link" href="#homepage-content-main">Skip to Smart Muslim homepage content</a>

    <div class="homepage-editor-shell">
        <header class="homepage-editor-topbar">
            <a class="admin-brand" href="{{ route('admin.dashboard') }}">
                <span class="admin-brand-mark" aria-hidden="true">
                    <img src="{{ asset('images/smart-holidays-logo.png') }}" alt="">
                </span>
                <span><strong>SMART</strong><small>HOLIDAYS ADMIN</small></span>
            </a>
            <nav class="homepage-editor-actions" aria-label="Admin actions">
                <a href="{{ route('admin.dashboard') }}">← Dashboard</a>
                <a href="{{ route('smart-muslim') }}" target="_blank" rel="noreferrer">View website ↗</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit">Sign out</button>
                </form>
            </nav>
        </header>

        <main class="homepage-editor-main" id="homepage-content-main">
            <div class="admin-breadcrumb homepage-editor-breadcrumb">
                <a href="{{ route('admin.dashboard') }}">Workspace</a>
                <span aria-hidden="true">/</span>
                <strong>Smart Muslim Homepage content</strong>
            </div>

            <section class="homepage-editor-heading">
                <div>
                    <span class="admin-eyebrow">WEBSITE MANAGEMENT</span>
                    <h1>Smart Muslim Homepage content</h1>
                    <p>Manage your featured slideshow and the services displayed on your Smart Muslim homepage.</p>
                </div>
                <div class="homepage-campaign-heading-actions">
                    <span class="homepage-editor-count">{{ $campaigns->count() }} featured campaigns</span>
                    <a class="admin-primary-button" href="{{ route('admin.homepage.create') }}">Add campaign <span aria-hidden="true">＋</span></a>
                </div>
            </section>

            @if (session('status'))
                <div class="homepage-editor-alert" role="status">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="homepage-editor-errors" role="alert">
                    <strong>Please check the following:</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <section class="homepage-campaign-grid" aria-label="Smart Muslim Homepage campaigns">
                @forelse ($campaigns as $campaign)
                    <article class="homepage-campaign-card">
                        <img src="{{ $campaign->imageUrl($campaign->image_main) }}" alt="{{ $campaign->image_alt }}" loading="lazy">
                        <div class="homepage-campaign-card-content">
                            <span class="homepage-campaign-kicker">{{ $campaign->kicker }}</span>
                            <h2>{{ $campaign->headline }} <strong>{{ $campaign->headline_highlight }}</strong></h2>
                            <p>{{ $campaign->body }}</p>
                            <div class="homepage-campaign-card-actions">
                                <a class="admin-primary-button" href="{{ route('admin.homepage.edit', $campaign) }}">
                                    Edit campaign <span aria-hidden="true">→</span>
                                </a>
                                @if ($campaigns->count() > 1)
                                    <form class="homepage-campaign-delete-form" method="POST" action="{{ route('admin.homepage.destroy', $campaign) }}" onsubmit="return confirm('Delete this Smart Muslim homepage campaign?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="homepage-campaign-delete" type="submit">Delete campaign</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="homepage-editor-empty">
                        Smart Muslim Homepage campaigns are not set up yet. Apply the latest database migrations to add them.
                    </div>
                @endforelse
            </section>

            <section class="homepage-services-admin" aria-labelledby="homepage-services-title">
                <div class="homepage-services-admin-heading">
                    <div>
                        <span class="admin-eyebrow">SERVICE CARDS</span>
                        <h2 id="homepage-services-title">Our services</h2>
                        <p>Edit the Smart Muslim homepage section title, service labels, images, and enquiry links.</p>
                    </div>
                    <a class="admin-primary-button" href="{{ route('admin.homepage.services') }}">Manage services <span aria-hidden="true">→</span></a>
                </div>
            </section>
        </main>

        <footer class="homepage-editor-footer">Smart Holidays · Website content manager</footer>
    </div>
</body>
</html>
