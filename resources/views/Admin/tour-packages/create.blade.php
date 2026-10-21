<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f4f7fc">
    <title>Create new page | Smart Holidays Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="admin-page">
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
            </nav>
        </header>

        <main class="homepage-editor-main" id="tour-package-create-main">
            <section class="homepage-editor-heading">
                <div>
                    <span class="admin-eyebrow">TOUR PACKAGE (PRIVATE)</span>
                    <h1>Create new page</h1>
                    <p>The page is published at <code>/tour/your-page-name</code> and added to the website navbar and this sidebar{{ $parent ? ', inside '.$parent->name : '' }}.</p>
                </div>
            </section>

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

            <form class="homepage-campaign-form" method="POST" action="{{ route('admin.tour-packages.store') }}">
                @csrf
                @if ($parent)
                    <input type="hidden" name="parent_slug" value="{{ $parent->slug }}">
                @endif
                <section class="homepage-form-panel">
                    <div class="homepage-form-grid">
                        <label class="homepage-form-field homepage-form-field-wide"><span>Page name</span><input name="name" value="{{ old('name') }}" required maxlength="60" autofocus placeholder="e.g. Malaysia"></label>
                    </div>
                    @php($selectedKind = old('kind', $kind))
                    <fieldset class="tour-package-kind-options">
                        <legend>Page type</legend>
                        <label class="tour-package-kind-option">
                            <input type="radio" name="kind" value="introduction" @checked($selectedKind === 'introduction')>
                            <span><strong>Introduction page</strong><small>Hero, introduction, three highlights, destination slideshow and contact — like Thailand or Vietnam.</small></span>
                        </label>
                        <label class="tour-package-kind-option">
                            <input type="radio" name="kind" value="package" @checked($selectedKind === 'package')>
                            <span><strong>Package page</strong><small>Hero, itineraries with day-by-day plans, gallery, inclusions, exclusions and hotels — like Bali or Aceh.</small></span>
                        </label>
                    </fieldset>
                </section>

                <div class="island-admin-form-actions">
                    <a class="homepage-editor-back" href="{{ route('admin.dashboard') }}">Cancel</a>
                    <button class="admin-primary-button" type="submit">Create page <span aria-hidden="true">→</span></button>
                </div>
            </form>
        </main>
    </div>
</body>
</html>
