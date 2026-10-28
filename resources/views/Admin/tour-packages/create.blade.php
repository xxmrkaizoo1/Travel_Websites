<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f4f7fc">
    <title>Create new page | Smart Holidays Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('Admin.partials.theme-head')
    <style>@view-transition { navigation: auto; }</style>
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
                    <span class="admin-eyebrow">{{ $smartMuslimMenu ? 'SMART MUSLIM TOUR · '.$smartMuslimMenu->label : ($isInsideSeries ? 'TOUR PACKAGE (SERIES)' : 'TOUR PACKAGE (PRIVATE)') }}</span>
                    <h1>Create new page</h1>
                    <p>The page is published at <code>/tour/your-page-name</code> and added to the website navbar and this sidebar{{ $smartMuslimMenu ? ', inside '.$smartMuslimMenu->label : ($parent ? ', inside '.$parent->name : '') }}.</p>
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

            <form class="homepage-campaign-form" method="POST" action="{{ $smartMuslimMenu ? route('admin.smart-muslim.menus.pages.store', $smartMuslimMenu) : route('admin.tour-packages.store') }}">
                @csrf
                @if ($parent)
                    <input type="hidden" name="parent_slug" value="{{ $parent->slug }}">
                @endif
                <section class="homepage-form-panel create-page-panel">
                    <div class="create-step">
                        <span class="create-step-number" aria-hidden="true">1</span>
                        <div class="create-step-body">
                            <h2>Name your page</h2>
                            <label class="homepage-form-field"><span class="visually-hidden">Page name</span><input name="name" value="{{ old('name') }}" required maxlength="60" autofocus placeholder="e.g. Malaysia"></label>
                        </div>
                    </div>

                    @php($selectedKind = old('kind', $kind))
                    <div class="create-step">
                        <span class="create-step-number" aria-hidden="true">2</span>
                        <div class="create-step-body">
                            <h2>Choose the page type</h2>
                            <div class="create-type-grid">
                                <label class="create-type-card">
                                    <input type="radio" name="kind" value="introduction" @checked($selectedKind === 'introduction')>
                                    <span class="create-type-icon" aria-hidden="true">🏝️</span>
                                    <strong>Introduction page</strong>
                                    <small>A landing page for a destination or group: hero, introduction, highlights, destinations and contact.</small>
                                    <span class="create-type-check" aria-hidden="true">✓</span>
                                </label>
                                <label class="create-type-card">
                                    <input type="radio" name="kind" value="package" @checked($selectedKind === 'package')>
                                    <span class="create-type-icon" aria-hidden="true">🧳</span>
                                    <strong>Package page</strong>
                                    <small>A sellable package: hero, itineraries, gallery, inclusions, exclusions and hotels.</small>
                                    <span class="create-type-check" aria-hidden="true">✓</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="create-step">
                        <span class="create-step-number" aria-hidden="true">3</span>
                        <div class="create-step-body">
                            <h2>Pick a template</h2>
                            @foreach (array_keys(\App\Models\TourPackagePage::TEMPLATES) as $templateKind)
                                <div class="create-template-grid" data-template-group="{{ $templateKind }}" @if ($selectedKind !== $templateKind) hidden @endif>
                                    @foreach (\App\Models\TourPackagePage::templatesFor($templateKind, $isInsideSeries) as $templateKey => [$templateName, $templateDescription])
                                        <label class="create-template-card">
                                            <input type="radio" name="template" value="{{ $templateKey }}" @checked(old('template', \App\Models\TourPackagePage::defaultTemplate($templateKind, $isInsideSeries)) === $templateKey) @disabled($selectedKind !== $templateKind)>
                                            @include('Admin.tour-packages.partials.template-preview', ['key' => $templateKey, 'name' => $templateName])
                                            <strong>{{ $templateName }}</strong>
                                            <small>{{ $templateDescription }}</small>
                                            <span class="create-type-check" aria-hidden="true">✓</span>
                                        </label>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="create-step">
                        <span class="create-step-number" aria-hidden="true">4</span>
                        <div class="create-step-body">
                            <h2>Choose a theme colour</h2>
                            <p class="create-step-hint">Colours the banner, labels, buttons and contact box on the public page. You can change it later.</p>
                            @include('Admin.tour-packages.partials.theme-picker', ['selectedTheme' => old('theme', \App\Models\TourPackagePage::DEFAULT_THEME)])
                        </div>
                    </div>
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
