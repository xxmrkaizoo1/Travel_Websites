<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f4f7fc">
    <meta name="description" content="Manage the Smart Holidays contact page.">
    <title>Contact Us | Smart Holidays Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>@view-transition { navigation: auto; }</style>
</head>
<body class="admin-page">
    <a class="skip-link" href="#contact-admin-main">Skip to contact content</a>

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
                <a href="{{ route('contact') }}" target="_blank" rel="noreferrer">View page ↗</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit">Sign out</button>
                </form>
            </nav>
        </header>

        <main class="homepage-editor-main" id="contact-admin-main">
            <div class="admin-breadcrumb homepage-editor-breadcrumb">
                <a href="{{ route('admin.dashboard') }}">Workspace</a>
                <span aria-hidden="true">/</span>
                <span>Smart Holidays</span>
                <span aria-hidden="true">/</span>
                <strong>Contact Us</strong>
            </div>

            <section class="homepage-editor-heading">
                <div>
                    <span class="admin-eyebrow">SMART HOLIDAYS</span>
                    <h1>Contact Us page</h1>
                    <p>Update the banner, company offices and the map.</p>
                </div>
                <span class="homepage-editor-count">{{ count($page->companies) }} companies</span>
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

            <form class="homepage-campaign-form" data-form-slideshow method="POST" action="{{ route('admin.contact.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <section class="homepage-form-panel">
                    <div class="homepage-form-panel-heading">
                        <span class="homepage-form-icon" aria-hidden="true">01</span>
                        <div><h2>Banner &amp; page details</h2><p>The banner at the top of the page, plus the browser title and search description. Clear the image to go back to the Smart Holidays banner.</p></div>
                    </div>
                    <div class="homepage-form-grid">
                        <label class="homepage-form-field"><span>Tag</span><input name="hero_eyebrow" value="{{ old('hero_eyebrow', $page->hero_eyebrow) }}" required maxlength="60"></label>
                        <label class="homepage-form-field"><span>Heading</span><input name="hero_title" value="{{ old('hero_title', $page->hero_title) }}" required maxlength="120"></label>
                        <label class="homepage-form-field homepage-form-field-wide"><span>Text</span><textarea name="hero_body" required maxlength="500">{{ old('hero_body', $page->hero_body) }}</textarea></label>
                        @php $heroImage = old('hero_image', $page->hero_image); @endphp
                        @include('Admin.partials.image-clear-field', [
                            'value' => (string) $heroImage,
                            'previewSrc' => filled($heroImage) ? $page->imageUrl($heroImage) : '',
                            'urlAttributes' => 'name="hero_image"',
                            'fileAttributes' => 'name="hero_image_file"',
                            'defaultTitle' => 'Default banner',
                            'defaultHint' => 'The Smart Holidays banner shows until you add your own',
                        ])
                        <label class="homepage-form-field"><span>Browser title</span><input name="meta_title" value="{{ old('meta_title', $page->meta_title) }}" required maxlength="120"></label>
                        <label class="homepage-form-field homepage-form-field-wide"><span>Search description</span><textarea name="meta_description" required maxlength="500">{{ old('meta_description', $page->meta_description) }}</textarea></label>
                    </div>
                </section>

                <section class="homepage-form-panel">
                    <div class="homepage-form-panel-heading">
                        <span class="homepage-form-icon" aria-hidden="true">02</span>
                        <div><h2>Contact information</h2><p>Each company office with its logo, address, phone, WhatsApp, email and website. Phone and WhatsApp numbers become tap-to-call and tap-to-chat links. Up to {{ \App\Models\ContactPage::MAX_COMPANIES }}; empty rows are ignored.</p></div>
                    </div>
                    <div class="homepage-form-grid">
                        <label class="homepage-form-field homepage-form-field-wide"><span>Heading</span><input name="companies_heading" value="{{ old('companies_heading', $page->companies_heading) }}" required maxlength="120"></label>
                    </div>

                    @php
                        $maxCompanies = \App\Models\ContactPage::MAX_COMPANIES;
                        $companyValues = array_values(array_filter(old('companies', $page->companies), 'is_array'));
                    @endphp
                    <div class="island-gallery-editor" data-gallery-editor data-max-slides="{{ $maxCompanies }}" data-field-prefix="companies" data-item-label="Company" data-item-label-plural="companies" data-gallery-paged="name" data-gallery-columns>
                        <div class="island-destination-fields-grid" data-gallery-slides>
                            @foreach ($companyValues as $index => $company)
                                <fieldset class="island-entry-panel" data-gallery-slide>
                                    <legend data-slide-legend>Company {{ $index + 1 }}</legend>
                                    @if (! empty($company['logo']))
                                        <img class="tour-package-slide-preview" src="{{ $page->imageUrl($company['logo']) }}" alt="" loading="lazy" width="160">
                                    @endif
                                    <label class="homepage-form-field"><span>Company name</span><input data-gallery-field="name" data-gallery-optional name="companies[{{ $index }}][name]" value="{{ $company['name'] ?? '' }}" maxlength="120"></label>
                                    <label class="homepage-form-field homepage-form-field-wide"><span>Address</span><input data-gallery-field="address" data-gallery-optional name="companies[{{ $index }}][address]" value="{{ $company['address'] ?? '' }}" maxlength="255"></label>
                                    <label class="homepage-form-field"><span>Phone <small class="homepage-field-hint">Optional · separate numbers with /</small></span><input data-gallery-field="phone" data-gallery-optional name="companies[{{ $index }}][phone]" value="{{ $company['phone'] ?? '' }}" maxlength="120"></label>
                                    <label class="homepage-form-field"><span>WhatsApp <small class="homepage-field-hint">Optional · separate numbers with /</small></span><input data-gallery-field="whatsapp" data-gallery-optional name="companies[{{ $index }}][whatsapp]" value="{{ $company['whatsapp'] ?? '' }}" maxlength="120"></label>
                                    <label class="homepage-form-field"><span>Email <small class="homepage-field-hint">Optional</small></span><input type="email" data-gallery-field="email" data-gallery-optional name="companies[{{ $index }}][email]" value="{{ $company['email'] ?? '' }}" maxlength="120"></label>
                                    <label class="homepage-form-field"><span>Website <small class="homepage-field-hint">Optional · e.g. www.smartholidays.com.my</small></span><input data-gallery-field="website" data-gallery-optional name="companies[{{ $index }}][website]" value="{{ $company['website'] ?? '' }}" maxlength="120"></label>
                                    <label class="homepage-form-field"><span>Logo URL <small class="homepage-field-hint">Optional · or upload a file</small></span><input data-gallery-field="logo" data-gallery-optional name="companies[{{ $index }}][logo]" value="{{ $company['logo'] ?? '' }}" maxlength="2048"></label>
                                    <label class="homepage-form-field"><span>Upload logo <small class="homepage-field-hint">Square PNG works best · replaces the URL</small></span><input type="file" data-gallery-field="logo_file" data-gallery-optional name="companies[{{ $index }}][logo_file]" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"></label>
                                    <button class="island-remove-slide" type="button" data-remove-slide aria-label="Remove company {{ $index + 1 }}">Remove company</button>
                                </fieldset>
                            @endforeach
                        </div>
                        <div class="island-gallery-editor-actions">
                            <span data-gallery-count aria-live="polite">{{ count($companyValues) }} of {{ $maxCompanies }} companies</span>
                            <button class="island-add-slide" type="button" data-add-slide>Add company <span aria-hidden="true">＋</span></button>
                        </div>
                    </div>

                    <template data-gallery-template>
                        <fieldset class="island-entry-panel" data-gallery-slide>
                            <legend data-slide-legend></legend>
                            <label class="homepage-form-field"><span>Company name</span><input data-gallery-field="name" data-gallery-optional maxlength="120"></label>
                            <label class="homepage-form-field homepage-form-field-wide"><span>Address</span><input data-gallery-field="address" data-gallery-optional maxlength="255"></label>
                            <label class="homepage-form-field"><span>Phone <small class="homepage-field-hint">Optional · separate numbers with /</small></span><input data-gallery-field="phone" data-gallery-optional maxlength="120"></label>
                            <label class="homepage-form-field"><span>WhatsApp <small class="homepage-field-hint">Optional · separate numbers with /</small></span><input data-gallery-field="whatsapp" data-gallery-optional maxlength="120"></label>
                            <label class="homepage-form-field"><span>Email <small class="homepage-field-hint">Optional</small></span><input type="email" data-gallery-field="email" data-gallery-optional maxlength="120"></label>
                            <label class="homepage-form-field"><span>Website <small class="homepage-field-hint">Optional · e.g. www.smartholidays.com.my</small></span><input data-gallery-field="website" data-gallery-optional maxlength="120"></label>
                            <label class="homepage-form-field"><span>Logo URL <small class="homepage-field-hint">Optional · or upload a file</small></span><input data-gallery-field="logo" data-gallery-optional maxlength="2048"></label>
                            <label class="homepage-form-field"><span>Upload logo <small class="homepage-field-hint">Square PNG works best · replaces the URL</small></span><input type="file" data-gallery-field="logo_file" data-gallery-optional accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"></label>
                            <button class="island-remove-slide" type="button" data-remove-slide>Remove company</button>
                        </fieldset>
                    </template>
                </section>

                <section class="homepage-form-panel">
                    <div class="homepage-form-panel-heading">
                        <span class="homepage-form-icon" aria-hidden="true">03</span>
                        <div><h2>Map</h2><p>The map at the end of the page shows the place you type in, with a "Get directions" button.</p></div>
                    </div>
                    <div class="homepage-form-grid">
                        <label class="homepage-form-field homepage-form-field-wide"><span>Map location <small class="homepage-field-hint">A business name or address, as you would type it into Google Maps</small></span><input name="map_query" value="{{ old('map_query', $page->map_query) }}" required maxlength="255"></label>
                    </div>
                </section>

                <div class="island-admin-form-actions">
                    <button class="admin-primary-button" type="submit">Save contact page <span aria-hidden="true">→</span></button>
                </div>
            </form>
        </main>
    </div>
</body>
</html>
