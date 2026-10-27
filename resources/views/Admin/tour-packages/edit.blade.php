<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f4f7fc">
    <meta name="description" content="Manage {{ $page->name }} private tour page content.">
    <title>{{ $page->name }} | Smart Holidays Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>@view-transition { navigation: auto; }</style>
</head>
<body class="admin-page">
    <a class="skip-link" href="#tour-package-admin-main">Skip to {{ $page->name }} content</a>

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
                <a href="{{ $page->publicUrl() }}" target="_blank" rel="noreferrer">View page ↗</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit">Sign out</button>
                </form>
            </nav>
        </header>

        <main class="homepage-editor-main" id="tour-package-admin-main">
            <div class="admin-breadcrumb homepage-editor-breadcrumb">
                <a href="{{ route('admin.dashboard') }}">Workspace</a>
                <span aria-hidden="true">/</span>
                <span>{{ $page->sectionLabel() }}</span>
                <span aria-hidden="true">/</span>
                <strong>{{ $page->name }}</strong>
            </div>

            <section class="homepage-editor-heading">
                <div>
                    <span class="admin-eyebrow">{{ strtoupper($page->sectionLabel()) }}</span>
                    <h1>{{ $page->name }}</h1>
                    <p>{{ $page->hasItineraries() ? 'Update the page text, itineraries, gallery, inclusions and hotels.' : ($page->isSeries() ? 'Update the page text, destinations, advantages, flags and photos.' : 'Update the page text, images and destination slideshow.') }}</p>
                </div>
                <span class="homepage-editor-count">{{ $page->hasItineraries() ? 'Package page' : ($page->isSeries() ? count($page->package_data['series']).' destinations' : count($slides).' slides') }}</span>
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

            @include('Admin.partials.booking-switch', ['package' => $page])

            <form class="homepage-campaign-form" data-form-slideshow method="POST" action="{{ route('admin.tour-packages.update', $page) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                @if ($page->is_custom)
                    <section class="homepage-form-panel">
                        <div class="homepage-form-panel-heading">
                            <span class="homepage-form-icon" aria-hidden="true">00</span>
                            <div><h2>Page name</h2><p>{{ $page->smartMuslimMenu ? 'Shown inside Smart Muslim Tour → '.$page->smartMuslimMenu->label.' in the website navbar and this sidebar.' : 'Shown in the website navbar and this sidebar.' }} The address <code>{{ parse_url($page->publicUrl(), PHP_URL_PATH) }}</code> stays the same.</p></div>
                        </div>
                        <div class="homepage-form-grid">
                            <label class="homepage-form-field"><span>Name</span><input name="name" value="{{ old('name', $page->name) }}" required maxlength="60"></label>
                            @unless ($page->smartMuslimMenu)
                                <label class="homepage-form-field"><span>Navigation parent</span>
                                    <select name="parent_slug">
                                        @foreach ($parentOptions as $parentSlug => $parentName)
                                            <option value="{{ $parentSlug }}" @selected((string) old('parent_slug', $page->parent_slug) === (string) $parentSlug)>{{ $parentName }}</option>
                                        @endforeach
                                    </select>
                                </label>
                            @endunless
                            <div class="homepage-form-field homepage-form-field-wide">
                                <span>Theme colour <small class="homepage-field-hint">Colours the banner, labels, buttons and contact box on the public page</small></span>
                                @include('Admin.tour-packages.partials.theme-picker', ['selectedTheme' => old('theme', $page->theme ?? \App\Models\TourPackagePage::DEFAULT_THEME)])
                            </div>
                        </div>
                    </section>
                @endif

                <section class="homepage-form-panel">
                    <div class="homepage-form-panel-heading">
                        <span class="homepage-form-icon" aria-hidden="true">01</span>
                        <div><h2>Hero section</h2><p>The opening message and image at the top of the public page.</p></div>
                    </div>
                    <div class="homepage-form-grid">
                        <label class="homepage-form-field"><span>Eyebrow</span><input name="hero_eyebrow" value="{{ old('hero_eyebrow', $page->hero_eyebrow) }}" required maxlength="120"></label>
                        <label class="homepage-form-field"><span>Small hero note</span><input name="hero_note" value="{{ old('hero_note', $page->hero_note) }}" required maxlength="120"></label>
                        <label class="homepage-form-field"><span>Headline</span><input name="hero_headline" value="{{ old('hero_headline', $page->hero_headline) }}" required maxlength="120"></label>
                        <label class="homepage-form-field"><span>Highlighted headline</span><input name="hero_highlight" value="{{ old('hero_highlight', $page->hero_highlight) }}" required maxlength="160"></label>
                        <label class="homepage-form-field homepage-form-field-wide"><span>Introductory text</span><textarea name="hero_body" required maxlength="1500">{{ old('hero_body', $page->hero_body) }}</textarea></label>
                        <label class="homepage-form-field"><span>Button label</span><input name="hero_cta_label" value="{{ old('hero_cta_label', $page->hero_cta_label) }}" required maxlength="100"></label>
                        <label class="homepage-image-field">
                            <span>Hero image <small class="homepage-field-hint">Leave empty to keep current image</small></span>
                            <img src="{{ $page->imageUrl($page->hero_image) }}" alt="" loading="lazy">
                            <input type="file" name="hero_image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                            @error('hero_image') <small>{{ $message }}</small> @enderror
                        </label>
                    </div>
                </section>

                <section class="homepage-form-panel">
                    <div class="homepage-form-panel-heading">
                        <span class="homepage-form-icon" aria-hidden="true">02</span>
                        <div><h2>Introduction</h2><p>Welcome text and a supporting photo.</p></div>
                    </div>
                    <div class="homepage-form-grid">
                        <label class="homepage-form-field"><span>Eyebrow</span><input name="intro_eyebrow" value="{{ old('intro_eyebrow', $page->intro_eyebrow) }}" required maxlength="120"></label>
                        <label class="homepage-form-field"><span>Headline</span><input name="intro_headline" value="{{ old('intro_headline', $page->intro_headline) }}" required maxlength="160"></label>
                        <label class="homepage-form-field"><span>Highlighted headline</span><input name="intro_highlight" value="{{ old('intro_highlight', $page->intro_highlight) }}" required maxlength="100"></label>
                        <label class="homepage-form-field"><span>Link label</span><input name="intro_link_label" value="{{ old('intro_link_label', $page->intro_link_label) }}" required maxlength="120"></label>
                        <label class="homepage-form-field homepage-form-field-wide"><span>Description</span><textarea name="intro_body" required maxlength="1500">{{ old('intro_body', $page->intro_body) }}</textarea></label>
                        <label class="homepage-form-field"><span>Image alt text</span><input name="intro_image_alt" value="{{ old('intro_image_alt', $page->intro_image_alt) }}" required maxlength="255"></label>
                       <label class="homepage-form-field"><span>Image caption</span><input name="intro_image_caption" value="{{ old('intro_image_caption', $page->intro_image_caption) }}" required maxlength="120"></label>
                        <label class="homepage-image-field homepage-form-field-wide">
                            <span>Introduction image <small class="homepage-field-hint">Leave empty to keep current image</small></span>
                            <img src="{{ $page->imageUrl($page->intro_image) }}" alt="" loading="lazy">
                            <input type="file" name="intro_image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                            @error('intro_image') <small>{{ $message }}</small> @enderror
                        </label>
                    </div>
                </section>

                <section class="homepage-form-panel">
                    <div class="homepage-form-panel-heading">
                        <span class="homepage-form-icon" aria-hidden="true">03</span>
                        <div><h2>Highlights</h2><p>Three short cards below the introduction.</p></div>
                    </div>
                    <div class="homepage-form-grid">
                        <label class="homepage-form-field"><span>Eyebrow</span><input name="highlights_eyebrow" value="{{ old('highlights_eyebrow', $page->highlights_eyebrow) }}" required maxlength="120"></label>
                        <label class="homepage-form-field"><span>Section headline</span><input name="highlights_headline" value="{{ old('highlights_headline', $page->highlights_headline) }}" required maxlength="160"></label>
                        <label class="homepage-form-field homepage-form-field-wide"><span>Section text</span><input name="highlights_body" value="{{ old('highlights_body', $page->highlights_body) }}" required maxlength="255"></label>
                    </div>
                    <div class="island-destination-fields-grid">
                        @foreach ($page->highlights as $index => $highlight)
                            <fieldset class="island-entry-panel">
                                <legend>Highlight {{ $index + 1 }}</legend>
                                <label class="homepage-form-field"><span>Title</span><input name="highlights[{{ $index }}][title]" value="{{ old('highlights.'.$index.'.title', $highlight['title']) }}" required maxlength="100"></label>
                                <label class="homepage-form-field"><span>Text</span><textarea name="highlights[{{ $index }}][body]" required maxlength="500">{{ old('highlights.'.$index.'.body', $highlight['body']) }}</textarea></label>
                            </fieldset>
                        @endforeach
                    </div>
                </section>

                @if ($page->hasItineraries())
                    @include('Admin.tour-packages.partials.package-fields')
                @elseif ($page->isSeries())
                    @include('Admin.tour-packages.partials.series-fields')
                @else
                <section class="homepage-form-panel">
                    <div class="homepage-form-panel-heading">
                        <span class="homepage-form-icon" aria-hidden="true">04</span>
                        <div><h2>Destination slideshow</h2><p>Add, edit or remove slides (up to {{ $maxSlides }}). Upload an image or paste an http(s) image URL.</p></div>
                    </div>
                    <div class="homepage-form-grid">
                        <label class="homepage-form-field"><span>Eyebrow</span><input name="destinations_eyebrow" value="{{ old('destinations_eyebrow', $page->destinations_eyebrow) }}" required maxlength="120"></label>
                        <label class="homepage-form-field"><span>Section headline</span><input name="destinations_headline" value="{{ old('destinations_headline', $page->destinations_headline) }}" required maxlength="160"></label>
                        <label class="homepage-form-field homepage-form-field-wide"><span>Section text</span><input name="destinations_body" value="{{ old('destinations_body', $page->destinations_body) }}" required maxlength="255"></label>
                    </div>

                    @php
                        $slideValues = array_values(array_filter(old('slides', $slides), 'is_array'));
                        $slideValues = $slideValues ?: [['image' => '', 'alt' => '', 'eyebrow' => '', 'title' => '', 'body' => '', 'link_label' => '', 'link_url' => '']];
                    @endphp
                    <div class="island-gallery-editor" data-gallery-editor data-max-slides="{{ $maxSlides }}" data-field-prefix="slides" data-gallery-paged="title" data-gallery-columns>
                        <div class="island-destination-fields-grid" data-gallery-slides>
                            @foreach ($slideValues as $index => $slide)
                                <fieldset class="island-entry-panel" data-gallery-slide>
                                    <legend data-slide-legend>Slide {{ $index + 1 }}</legend>
                                    @if (! empty($slide['image']))
                                        <img class="tour-package-slide-preview" src="{{ $page->imageUrl($slide['image']) }}" alt="" loading="lazy" width="160">
                                    @endif
                                    <label class="homepage-form-field"><span>Image URL <small class="homepage-field-hint">Or upload a file below</small></span><input data-gallery-field="image" data-gallery-optional name="slides[{{ $index }}][image]" value="{{ $slide['image'] ?? '' }}" maxlength="2048"></label>
                                    <label class="homepage-form-field"><span>Upload image <small class="homepage-field-hint">Replaces the URL above</small></span><input type="file" data-gallery-field="image_file" data-gallery-optional name="slides[{{ $index }}][image_file]" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"></label>
                                    <label class="homepage-form-field"><span>Image alt text</span><input data-gallery-field="alt" name="slides[{{ $index }}][alt]" value="{{ $slide['alt'] ?? '' }}" maxlength="255" @if ($loop->first) required @endif></label>
                                    <label class="homepage-form-field"><span>Eyebrow</span><input data-gallery-field="eyebrow" name="slides[{{ $index }}][eyebrow]" value="{{ $slide['eyebrow'] ?? '' }}" maxlength="120" @if ($loop->first) required @endif></label>
                                    <label class="homepage-form-field"><span>Slide title</span><input data-gallery-field="title" name="slides[{{ $index }}][title]" value="{{ $slide['title'] ?? '' }}" maxlength="120" @if ($loop->first) required @endif></label>
                                    <label class="homepage-form-field homepage-form-field-wide"><span>Slide text</span><textarea data-gallery-field="body" name="slides[{{ $index }}][body]" maxlength="500" @if ($loop->first) required @endif>{{ $slide['body'] ?? '' }}</textarea></label>
                                    <label class="homepage-form-field"><span>Link label <small class="homepage-field-hint">Optional</small></span><input data-gallery-field="link_label" data-gallery-optional name="slides[{{ $index }}][link_label]" value="{{ $slide['link_label'] ?? '' }}" maxlength="120"></label>
                                    <label class="homepage-form-field"><span>Link URL <small class="homepage-field-hint">e.g. /bali or https://…</small></span><input data-gallery-field="link_url" data-gallery-optional name="slides[{{ $index }}][link_url]" value="{{ $slide['link_url'] ?? '' }}" maxlength="2048"></label>
                                    <button class="island-remove-slide" type="button" data-remove-slide aria-label="Remove slide {{ $index + 1 }}">Remove slide</button>
                                </fieldset>
                            @endforeach
                        </div>
                        <div class="island-gallery-editor-actions">
                            <span data-gallery-count aria-live="polite">{{ count($slideValues) }} of {{ $maxSlides }} slides</span>
                            <button class="island-add-slide" type="button" data-add-slide>Add slide <span aria-hidden="true">＋</span></button>
                        </div>
                    </div>

                    <template data-gallery-template>
                        <fieldset class="island-entry-panel" data-gallery-slide>
                            <legend data-slide-legend></legend>
                            <label class="homepage-form-field"><span>Image URL <small class="homepage-field-hint">Or upload a file below</small></span><input data-gallery-field="image" data-gallery-optional maxlength="2048"></label>
                            <label class="homepage-form-field"><span>Upload image <small class="homepage-field-hint">Replaces the URL above</small></span><input type="file" data-gallery-field="image_file" data-gallery-optional accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"></label>
                            <label class="homepage-form-field"><span>Image alt text</span><input data-gallery-field="alt" maxlength="255"></label>
                            <label class="homepage-form-field"><span>Eyebrow</span><input data-gallery-field="eyebrow" maxlength="120"></label>
                            <label class="homepage-form-field"><span>Slide title</span><input data-gallery-field="title" maxlength="120"></label>
                            <label class="homepage-form-field homepage-form-field-wide"><span>Slide text</span><textarea data-gallery-field="body" maxlength="500"></textarea></label>
                            <label class="homepage-form-field"><span>Link label <small class="homepage-field-hint">Optional</small></span><input data-gallery-field="link_label" data-gallery-optional maxlength="120"></label>
                            <label class="homepage-form-field"><span>Link URL <small class="homepage-field-hint">e.g. /bali or https://…</small></span><input data-gallery-field="link_url" data-gallery-optional maxlength="2048"></label>
                            <button class="island-remove-slide" type="button" data-remove-slide>Remove slide</button>
                        </fieldset>
                    </template>
                </section>
                @endif

                <section class="homepage-form-panel">
                    <div class="homepage-form-panel-heading">
                        <span class="homepage-form-icon" aria-hidden="true">{{ $page->hasItineraries() ? '08' : '05' }}</span>
                        <div><h2>Contact &amp; page details</h2><p>The enquiry section and the page's browser title and description.</p></div>
                    </div>
                    <div class="homepage-form-grid">
                        <label class="homepage-form-field"><span>Eyebrow</span><input name="contact_eyebrow" value="{{ old('contact_eyebrow', $page->contact_eyebrow) }}" required maxlength="120"></label>
                        <label class="homepage-form-field"><span>Headline</span><input name="contact_headline" value="{{ old('contact_headline', $page->contact_headline) }}" required maxlength="160"></label>
                        <label class="homepage-form-field homepage-form-field-wide"><span>Text</span><textarea name="contact_body" required maxlength="1500">{{ old('contact_body', $page->contact_body) }}</textarea></label>
                        <label class="homepage-form-field"><span>Button label</span><input name="contact_cta_label" value="{{ old('contact_cta_label', $page->contact_cta_label) }}" required maxlength="100"></label>
                        <label class="homepage-form-field"><span>Button URL <small class="homepage-field-hint">Use a full http:// or https:// URL</small></span><input type="url" name="contact_href" value="{{ old('contact_href', $page->contact_href) }}" required maxlength="2048"></label>
                        <label class="homepage-form-field"><span>Browser title</span><input name="meta_title" value="{{ old('meta_title', $page->meta_title) }}" required maxlength="160"></label>
                        <label class="homepage-form-field"><span>Meta description</span><input name="meta_description" value="{{ old('meta_description', $page->meta_description) }}" required maxlength="255"></label>
                    </div>
                </section>

                <div class="island-admin-form-actions">
                    <a class="homepage-editor-back" href="{{ route('admin.dashboard') }}">Cancel</a>
                    <button class="admin-primary-button" type="submit">Save {{ $page->name }} page <span aria-hidden="true">→</span></button>
                </div>
            </form>

            @if ($page->is_custom)
                <form class="tour-package-delete-form" method="POST" action="{{ route('admin.tour-packages.destroy', $page) }}" onsubmit="return confirm('Delete the {{ e($page->name) }} page permanently? This cannot be undone.')">
                    @csrf
                    @method('DELETE')
                    <button class="tour-package-delete-button" type="submit">Delete this page</button>
                </form>
            @endif
        </main>

        <footer class="homepage-editor-footer">Smart Holidays · {{ $page->name }} content manager</footer>
    </div>
</body>
</html>
