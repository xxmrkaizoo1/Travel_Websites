<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f4f7fc">
    <meta name="description" content="Manage the service cards on the Smart Muslim homepage.">
    <title>Our Services | Smart Holidays Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>@view-transition { navigation: auto; }</style>
</head>
<body class="admin-page">
    <a class="skip-link" href="#homepage-services-main">Skip to Smart Muslim homepage services</a>

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
                <a href="{{ route('admin.homepage') }}">Smart Muslim Homepage editor</a>
                <a href="{{ route('smart-muslim') }}" target="_blank" rel="noreferrer">View website ↗</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit">Sign out</button>
                </form>
            </nav>
        </header>

        <main class="homepage-editor-main" id="homepage-services-main">
            <div class="admin-breadcrumb homepage-editor-breadcrumb">
                <a href="{{ route('admin.homepage') }}">Smart Muslim Homepage content</a>
                <span aria-hidden="true">/</span>
                <strong>Our services</strong>
            </div>

            <section class="homepage-editor-heading">
                <div>
                    <span class="admin-eyebrow">WEBSITE MANAGEMENT</span>
                    <h1>Our services</h1>
                    <p>Manage the section title and all the service cards visitors see on your Smart Muslim homepage.</p>
                </div>
                <span class="homepage-editor-count">{{ $services->count() }} service cards</span>
            </section>

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

            <form class="homepage-service-heading-form homepage-form-panel" method="POST" action="{{ route('admin.homepage.services.heading') }}">
                @csrf
                @method('PUT')
                <label class="homepage-form-field">
                    <span>Section title</span>
                    <input name="heading" value="{{ old('heading', $heading) }}" maxlength="100" required>
                    @error('heading') <small>{{ $message }}</small> @enderror
                </label>
                <button class="admin-primary-button" type="submit">Save title <span aria-hidden="true">→</span></button>
            </form>

            <section class="homepage-campaign-grid homepage-service-grid" aria-label="Smart Muslim Homepage service cards">
                @forelse ($services as $service)
                    <article class="homepage-campaign-card homepage-service-card">
                        <img src="{{ $service->imageUrl() }}" alt="{{ $service->image_alt }}" loading="lazy">
                        <div class="homepage-campaign-card-content">
                            <span class="homepage-campaign-kicker">{{ $service->overlay_label }}</span>
                            <h2>{{ $service->title }}</h2>
                            <p>Button: {{ $service->button_label }}</p>
                            <a class="admin-primary-button" href="{{ route('admin.homepage.services.edit', $service) }}">
                                Edit service <span aria-hidden="true">→</span>
                            </a>
                        </div>
                    </article>
                @empty
                    <div class="homepage-editor-empty">
                        Smart Muslim Homepage services are not set up yet. Apply the latest database migrations to add them.
                    </div>
                @endforelse
            </section>
        </main>

        <footer class="homepage-editor-footer">Smart Holidays · Website content manager</footer>
    </div>
</body>
</html>
