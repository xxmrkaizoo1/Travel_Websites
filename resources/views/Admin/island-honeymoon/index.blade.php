<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f4f7fc">
    <meta name="description" content="Manage Island & Honeymoon page content and destination spotlights.">
    <title>Island &amp; Honeymoon | Smart Holidays Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="admin-page">
    <a class="skip-link" href="#island-admin-main">Skip to Island &amp; Honeymoon content</a>

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
                <a href="{{ route('island-honeymoon') }}" target="_blank" rel="noreferrer">View page ↗</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit">Sign out</button>
                </form>
            </nav>
        </header>

        <main class="homepage-editor-main" id="island-admin-main">
            <div class="admin-breadcrumb homepage-editor-breadcrumb">
                <a href="{{ route('admin.dashboard') }}">Workspace</a>
                <span aria-hidden="true">/</span>
                <strong>Island &amp; Honeymoon</strong>
            </div>

            <section class="homepage-editor-heading">
                <div>
                    <span class="admin-eyebrow">PRIVATE GETAWAYS</span>
                    <h1>Island &amp; Honeymoon</h1>
                    <p>Update the page story, images, highlights and destination spotlights.</p>
                </div>
                <span class="homepage-editor-count">{{ $destinations->count() }} destination spotlights</span>
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

            <form class="homepage-campaign-form" data-form-slideshow method="POST" action="{{ route('admin.island-honeymoon.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <section class="homepage-form-panel">
                    <div class="homepage-form-panel-heading">
                        <span class="homepage-form-icon" aria-hidden="true">01</span>
                        <div><h2>Hero section</h2><p>The opening message and image at the top of the public page.</p></div>
                    </div>
                    <div class="homepage-form-grid">
                        <label class="homepage-form-field">
                            <span>Eyebrow</span>
                            <input name="hero_eyebrow" value="{{ old('hero_eyebrow', $settings->hero_eyebrow) }}" required maxlength="120">
                        </label>
                        <label class="homepage-form-field">
                            <span>Small hero note</span>
                            <input name="hero_note" value="{{ old('hero_note', $settings->hero_note) }}" required maxlength="120">
                        </label>
                        <label class="homepage-form-field">
                            <span>Headline</span>
                            <input name="hero_headline" value="{{ old('hero_headline', $settings->hero_headline) }}" required maxlength="120">
                        </label>
                        <label class="homepage-form-field">
                            <span>Highlighted headline</span>
                            <input name="hero_highlight" value="{{ old('hero_highlight', $settings->hero_highlight) }}" required maxlength="160">
                        </label>
                        <label class="homepage-form-field homepage-form-field-wide">
                            <span>Introductory text</span>
                            <textarea name="hero_body" required maxlength="1500">{{ old('hero_body', $settings->hero_body) }}</textarea>
                        </label>
                        <label class="homepage-form-field">
                            <span>Button label</span>
                            <input name="hero_cta_label" value="{{ old('hero_cta_label', $settings->hero_cta_label) }}" required maxlength="100">
                        </label>
                        <label class="homepage-image-field">
                            <span>Hero image <small class="homepage-field-hint">Leave empty to keep current image</small></span>
                            <img src="{{ $settings->imageUrl($settings->hero_image) }}" alt="" loading="lazy">
                            <input type="file" name="hero_image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                            @error('hero_image') <small>{{ $message }}</small> @enderror
                        </label>
                    </div>
                </section>

                <section class="homepage-form-panel">
                    <div class="homepage-form-panel-heading">
                        <span class="homepage-form-icon" aria-hidden="true">02</span>
                        <div><h2>Introduction</h2><p>Describe the trip-planning experience and choose a supporting photo.</p></div>
                    </div>
                    <div class="homepage-form-grid">
                        <label class="homepage-form-field"><span>Eyebrow</span><input name="intro_eyebrow" value="{{ old('intro_eyebrow', $settings->intro_eyebrow) }}" required maxlength="120"></label>
                        <label class="homepage-form-field"><span>Headline</span><input name="intro_headline" value="{{ old('intro_headline', $settings->intro_headline) }}" required maxlength="160"></label>
                        <label class="homepage-form-field"><span>Highlighted headline</span><input name="intro_highlight" value="{{ old('intro_highlight', $settings->intro_highlight) }}" required maxlength="100"></label>
                        <label class="homepage-form-field"><span>Link label</span><input name="intro_link_label" value="{{ old('intro_link_label', $settings->intro_link_label) }}" required maxlength="120"></label>
                        <label class="homepage-form-field homepage-form-field-wide"><span>Description</span><textarea name="intro_body" required maxlength="1500">{{ old('intro_body', $settings->intro_body) }}</textarea></label>
                        <label class="homepage-form-field homepage-form-field-wide"><span>Link URL <small class="homepage-field-hint">Use a full http:// or https:// URL</small></span><input type="url" name="intro_link_href" value="{{ old('intro_link_href', $settings->intro_link_href) }}" required maxlength="2048"></label>
                        <label class="homepage-form-field"><span>Image alt text</span><input name="intro_image_alt" value="{{ old('intro_image_alt', $settings->intro_image_alt) }}" required maxlength="255"></label>
                        <label class="homepage-form-field"><span>Image caption</span><input name="intro_image_caption" value="{{ old('intro_image_caption', $settings->intro_image_caption) }}" required maxlength="120"></label>
                        <label class="homepage-image-field homepage-form-field-wide">
                            <span>Introduction image <small class="homepage-field-hint">Leave empty to keep current image</small></span>
                            <img src="{{ $settings->imageUrl($settings->intro_image) }}" alt="" loading="lazy">
                            <input type="file" name="intro_image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                            @error('intro_image') <small>{{ $message }}</small> @enderror
                        </label>
                    </div>
                </section>

                <section class="homepage-form-panel">
                    <div class="homepage-form-panel-heading">
                        <span class="homepage-form-icon" aria-hidden="true">03</span>
                        <div><h2>Trip highlights</h2><p>Keep three concise cards explaining how guests can shape their getaway.</p></div>
                    </div>
                    <div class="homepage-form-grid">
                        <label class="homepage-form-field"><span>Eyebrow</span><input name="highlights_eyebrow" value="{{ old('highlights_eyebrow', $settings->highlights_eyebrow) }}" required maxlength="120"></label>
                        <label class="homepage-form-field"><span>Section headline</span><input name="highlights_headline" value="{{ old('highlights_headline', $settings->highlights_headline) }}" required maxlength="160"></label>
                        <label class="homepage-form-field homepage-form-field-wide"><span>Section description</span><input name="highlights_body" value="{{ old('highlights_body', $settings->highlights_body) }}" required maxlength="255"></label>
                        @foreach (old('highlights', $settings->highlights) as $index => $highlight)
                            <label class="homepage-form-field"><span>Card {{ $index + 1 }} title</span><input name="highlights[{{ $index }}][title]" value="{{ $highlight['title'] }}" required maxlength="100"></label>
                            <label class="homepage-form-field"><span>Card {{ $index + 1 }} description</span><textarea name="highlights[{{ $index }}][body]" required maxlength="500">{{ $highlight['body'] }}</textarea></label>
                        @endforeach
                    </div>
                </section>

                <section class="homepage-form-panel">
                    <div class="homepage-form-panel-heading">
                        <span class="homepage-form-icon" aria-hidden="true">04</span>
                        <div><h2>Contact section</h2><p>The closing enquiry message and its destination.</p></div>
                    </div>
                    <div class="homepage-form-grid">
                        <label class="homepage-form-field"><span>Eyebrow</span><input name="contact_eyebrow" value="{{ old('contact_eyebrow', $settings->contact_eyebrow) }}" required maxlength="120"></label>
                        <label class="homepage-form-field"><span>Button label</span><input name="contact_cta_label" value="{{ old('contact_cta_label', $settings->contact_cta_label) }}" required maxlength="100"></label>
                        <label class="homepage-form-field homepage-form-field-wide"><span>Headline</span><input name="contact_headline" value="{{ old('contact_headline', $settings->contact_headline) }}" required maxlength="160"></label>
                        <label class="homepage-form-field homepage-form-field-wide"><span>Description</span><textarea name="contact_body" required maxlength="1500">{{ old('contact_body', $settings->contact_body) }}</textarea></label>
                        <label class="homepage-form-field homepage-form-field-wide"><span>Button URL</span><input type="url" name="contact_href" value="{{ old('contact_href', $settings->contact_href) }}" required maxlength="2048"></label>
                    </div>
                </section>

                <div class="island-admin-form-actions">
                    <button class="admin-primary-button" type="submit">Save page content <span aria-hidden="true">→</span></button>
                </div>
            </form>

            <section class="island-destination-admin" aria-labelledby="destination-admin-title">
                <div class="homepage-services-admin-heading">
                    <div>
                        <span class="admin-eyebrow">DESTINATION SPOTLIGHT</span>
                        <h2 id="destination-admin-title">Featured destinations</h2>
                        <p>Add a destination, update its travel details, benefits and slideshow, or remove it from the public page.</p>
                    </div>
                    <a class="admin-primary-button" href="{{ route('admin.island-honeymoon.destinations.create') }}">Add destination <span aria-hidden="true">＋</span></a>
                </div>

                <div class="island-destination-admin-grid">
                    @forelse ($destinations as $destination)
                        <article class="island-destination-admin-card">
                            <img src="{{ $destination->imageUrl($destination->feature_image) }}" alt="{{ $destination->feature_image_alt }}" loading="lazy">
                            <div class="island-destination-admin-card-content">
                                <span class="homepage-campaign-kicker">{{ $destination->country }} · {{ count($destination->gallery) }} slideshow slides</span>
                                <h3>{{ $destination->name }}</h3>
                                <p>{{ \Illuminate\Support\Str::limit($destination->description, 150) }}</p>
                                <div class="island-destination-admin-actions">
                                    <a class="admin-primary-button" href="{{ route('admin.island-honeymoon.destinations.edit', $destination) }}">Edit spotlight <span aria-hidden="true">→</span></a>
                                    <form method="POST" action="{{ route('admin.island-honeymoon.destinations.destroy', $destination) }}" onsubmit="return confirm('Delete this destination spotlight?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="island-delete-button" type="submit">Delete</button>
                                    </form>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="homepage-editor-empty">No destination spotlights have been added yet.</div>
                    @endforelse
                </div>
            </section>
        </main>

        <footer class="homepage-editor-footer">Smart Holidays · Island &amp; Honeymoon content manager</footer>
    </div>
</body>
</html>
