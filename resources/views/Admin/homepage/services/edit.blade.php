<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f4f7fc">
    <meta name="description" content="Edit a service card on the Smart Muslim homepage.">
    <title>Edit {{ $service->title }} | Smart Holidays Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('Admin.partials.theme-head')
    <style>@view-transition { navigation: auto; }</style>
</head>
<body class="admin-page">
    <a class="skip-link" href="#homepage-service-form">Skip to service form</a>

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
                <a href="{{ route('admin.homepage.services') }}">All services</a>
                <a href="{{ route('smart-muslim') }}" target="_blank" rel="noreferrer">View website ↗</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit">Sign out</button>
                </form>
            </nav>
        </header>

        <main class="homepage-editor-main" id="homepage-service-form">
            <div class="admin-breadcrumb homepage-editor-breadcrumb">
                <a href="{{ route('admin.homepage') }}">Smart Muslim Homepage content</a>
                <span aria-hidden="true">/</span>
                <a href="{{ route('admin.homepage.services') }}">Our services</a>
                <span aria-hidden="true">/</span>
                <strong>Edit service</strong>
            </div>

            <section class="homepage-editor-heading">
                <div>
                    <span class="admin-eyebrow">SMART MUSLIM HOMEPAGE SERVICE CARD</span>
                    <h1>Edit service</h1>
                    <p>Update the card title, image, overlay label, button text, or destination link.</p>
                </div>
                <a class="homepage-editor-back" href="{{ route('admin.homepage.services') }}">← All services</a>
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

            <form class="homepage-campaign-form" data-form-slideshow method="POST" action="{{ route('admin.homepage.services.update', $service) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <section class="homepage-form-panel">
                    <div class="homepage-form-panel-heading">
                        <span class="homepage-form-icon" aria-hidden="true">Aa</span>
                        <div>
                            <h2>Service labels and link</h2>
                            <p>Change the text and where visitors go when they select this service.</p>
                        </div>
                    </div>

                    <div class="homepage-form-grid">
                        <label class="homepage-form-field">
                            <span>Card title</span>
                            <input name="title" value="{{ old('title', $service->title) }}" maxlength="100" required>
                            @error('title') <small>{{ $message }}</small> @enderror
                        </label>
                        <label class="homepage-form-field">
                            <span>Button text</span>
                            <input name="button_label" value="{{ old('button_label', $service->button_label) }}" maxlength="100" required>
                            @error('button_label') <small>{{ $message }}</small> @enderror
                        </label>
                        <label class="homepage-form-field homepage-form-field-wide">
                            <span>Image overlay label <small>Put each line on a new line.</small></span>
                            <textarea name="overlay_label" rows="2" maxlength="100" required>{{ old('overlay_label', $service->overlay_label) }}</textarea>
                            @error('overlay_label') <small>{{ $message }}</small> @enderror
                        </label>
                        <label class="homepage-form-field homepage-form-field-wide">
                            <span>Button and image destination</span>
                            <input name="href" type="text" value="{{ old('href', $service->href) }}" maxlength="2048" required>
                            <span class="homepage-field-hint">Use a page on this site such as /china-muslim, an https:// or http:// URL, mailto: email address, or tel: phone number.</span>
                            @error('href') <small>{{ $message }}</small> @enderror
                        </label>
                    </div>
                </section>

                <section class="homepage-form-panel">
                    <div class="homepage-form-panel-heading">
                        <span class="homepage-form-icon" aria-hidden="true">▧</span>
                        <div>
                            <h2>Service image</h2>
                            <p>Choose an optional JPG, PNG, or WebP image up to 5 MB.</p>
                        </div>
                    </div>

                    <div class="homepage-image-grid homepage-image-grid-single">
                        <label class="homepage-image-field">
                            <img src="{{ $service->imageUrl() }}" alt="Current service image">
                            <span class="homepage-image-name">Current image</span>
                            <input type="file" name="image" accept="image/jpeg,image/png,image/webp">
                            @error('image') <small>{{ $message }}</small> @enderror
                        </label>
                    </div>

                    <label class="homepage-form-field homepage-alt-field">
                        <span>Image description <small>Accessibility text for screen readers.</small></span>
                        <input name="image_alt" value="{{ old('image_alt', $service->image_alt) }}" maxlength="255" required>
                        @error('image_alt') <small>{{ $message }}</small> @enderror
                    </label>
                </section>

                <div class="homepage-form-actions">
                    <a class="homepage-editor-back" href="{{ route('admin.homepage.services') }}">Cancel</a>
                    <button class="admin-primary-button" type="submit">Save service <span aria-hidden="true">→</span></button>
                </div>
            </form>
        </main>

        <footer class="homepage-editor-footer">Smart Holidays · Website content manager</footer>
    </div>
</body>
</html>
