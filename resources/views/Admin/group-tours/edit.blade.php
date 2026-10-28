<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f4f7fc">
    <meta name="description" content="Manage the Smart Holidays {{ $page->label() }} page.">
    <title>{{ $page->label() }} | Smart Holidays Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('Admin.partials.theme-head')
    <style>@view-transition { navigation: auto; }</style>
</head>
<body class="admin-page">
    <a class="skip-link" href="#group-tours-admin-main">Skip to {{ $page->label() }} content</a>

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

        <main class="homepage-editor-main" id="group-tours-admin-main">
            <div class="admin-breadcrumb homepage-editor-breadcrumb">
                <a href="{{ route('admin.dashboard') }}">Workspace</a>
                <span aria-hidden="true">/</span>
                <span>Smart Holidays</span>
                <span aria-hidden="true">/</span>
                <strong>{{ $page->label() }}</strong>
            </div>

            <section class="homepage-editor-heading">
                <div>
                    <span class="admin-eyebrow">SMART HOLIDAYS</span>
                    <h1>{{ $page->label() }} page</h1>
                    <p>Update the heading and the country cards on the {{ $page->label() }} page.</p>
                </div>
                <span class="homepage-editor-count">{{ count($page->tours) }} tour cards</span>
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

            <form class="homepage-campaign-form" data-form-slideshow method="POST" action="{{ route($page->adminRoute('update')) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <section class="homepage-form-panel">
                    <div class="homepage-form-panel-heading">
                        <span class="homepage-form-icon" aria-hidden="true">01</span>
                        <div><h2>Tour cards</h2><p>Each card shows a photo with the country name. Visitors can search the cards and filter them by their filter tag. Add up to {{ \App\Models\GroupToursPage::MAX_TOURS }}; empty cards are ignored. Cards without a link open a WhatsApp enquiry for that tour.</p></div>
                    </div>

                    @php
                        $maxTours = \App\Models\GroupToursPage::MAX_TOURS;
                        $tourValues = array_values(array_filter(old('tours', $page->tours), 'is_array'));
                        $tourValues = $tourValues ?: [array_fill_keys(['name', 'tagline', 'category', 'image', 'alt', 'link_url'], '')];
                    @endphp
                    <div class="island-gallery-editor" data-gallery-editor data-max-slides="{{ $maxTours }}" data-field-prefix="tours" data-item-label="Tour card" data-item-label-plural="tour cards" data-gallery-paged="name" data-gallery-columns>
                        <div class="island-destination-fields-grid" data-gallery-slides>
                            @foreach ($tourValues as $index => $tour)
                                <fieldset class="island-entry-panel" data-gallery-slide>
                                    <legend data-slide-legend>Tour card {{ $index + 1 }}</legend>
                                    @if (! empty($tour['image']))
                                        <img class="tour-package-slide-preview" src="{{ $page->imageUrl($tour['image']) }}" alt="" loading="lazy" width="160">
                                    @endif
                                    <label class="homepage-form-field"><span>Country or tour name</span><input data-gallery-field="name" name="tours[{{ $index }}][name]" value="{{ $tour['name'] ?? '' }}" maxlength="40" @if ($loop->first) required @endif></label>
                                    <label class="homepage-form-field"><span>Tagline <small class="homepage-field-hint">Optional · one short line under the name</small></span><input data-gallery-field="tagline" data-gallery-optional name="tours[{{ $index }}][tagline]" value="{{ $tour['tagline'] ?? '' }}" maxlength="120"></label>
                                    <label class="homepage-form-field"><span>Filter tag <small class="homepage-field-hint">Optional · e.g. Domestic or International</small></span><input data-gallery-field="category" data-gallery-optional name="tours[{{ $index }}][category]" value="{{ $tour['category'] ?? '' }}" maxlength="40" list="group-tour-categories"></label>
                                    <label class="homepage-form-field"><span>Link <small class="homepage-field-hint">Optional · a {{ $page->label() }} page, not a Smart Muslim page. Leave empty to open a WhatsApp enquiry</small></span><input data-gallery-field="link_url" data-gallery-optional name="tours[{{ $index }}][link_url]" value="{{ $tour['link_url'] ?? '' }}" maxlength="2048"></label>
                                    <label class="homepage-form-field"><span>Image URL <small class="homepage-field-hint">Or upload a file</small></span><input data-gallery-field="image" data-gallery-optional name="tours[{{ $index }}][image]" value="{{ $tour['image'] ?? '' }}" maxlength="2048"></label>
                                    <label class="homepage-form-field"><span>Upload image <small class="homepage-field-hint">Replaces the URL</small></span><input type="file" data-gallery-field="image_file" data-gallery-optional name="tours[{{ $index }}][image_file]" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"></label>
                                    <label class="homepage-form-field homepage-form-field-wide"><span>Image alt text</span><input data-gallery-field="alt" name="tours[{{ $index }}][alt]" value="{{ $tour['alt'] ?? '' }}" maxlength="255" @if ($loop->first) required @endif></label>
                                    <button class="island-remove-slide" type="button" data-remove-slide aria-label="Remove tour card {{ $index + 1 }}">Remove card</button>
                                </fieldset>
                            @endforeach
                        </div>
                        <div class="island-gallery-editor-actions">
                            <span data-gallery-count aria-live="polite">{{ count($tourValues) }} of {{ $maxTours }} tour cards</span>
                            <button class="island-add-slide" type="button" data-add-slide>Add tour card <span aria-hidden="true">＋</span></button>
                        </div>
                    </div>

                    <datalist id="group-tour-categories">
                        @foreach (array_unique(['Domestic', 'International', ...$page->tourCategories()]) as $category)
                            <option value="{{ $category }}"></option>
                        @endforeach
                    </datalist>

                    <template data-gallery-template>
                        <fieldset class="island-entry-panel" data-gallery-slide>
                            <legend data-slide-legend></legend>
                            <label class="homepage-form-field"><span>Country or tour name</span><input data-gallery-field="name" maxlength="40"></label>
                            <label class="homepage-form-field"><span>Tagline <small class="homepage-field-hint">Optional · one short line under the name</small></span><input data-gallery-field="tagline" data-gallery-optional maxlength="120"></label>
                            <label class="homepage-form-field"><span>Filter tag <small class="homepage-field-hint">Optional · e.g. Domestic or International</small></span><input data-gallery-field="category" data-gallery-optional maxlength="40" list="group-tour-categories"></label>
                            <label class="homepage-form-field"><span>Link <small class="homepage-field-hint">Optional · a {{ $page->label() }} page, not a Smart Muslim page. Leave empty to open a WhatsApp enquiry</small></span><input data-gallery-field="link_url" data-gallery-optional maxlength="2048"></label>
                            <label class="homepage-form-field"><span>Image URL <small class="homepage-field-hint">Or upload a file</small></span><input data-gallery-field="image" data-gallery-optional maxlength="2048"></label>
                            <label class="homepage-form-field"><span>Upload image <small class="homepage-field-hint">Replaces the URL</small></span><input type="file" data-gallery-field="image_file" data-gallery-optional accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"></label>
                            <label class="homepage-form-field homepage-form-field-wide"><span>Image alt text</span><input data-gallery-field="alt" maxlength="255"></label>
                            <button class="island-remove-slide" type="button" data-remove-slide>Remove card</button>
                        </fieldset>
                    </template>
                </section>

                <section class="homepage-form-panel">
                    <div class="homepage-form-panel-heading">
                        <span class="homepage-form-icon" aria-hidden="true">02</span>
                        <div><h2>Banner &amp; page details</h2><p>The banner at the top of the page (an image, with an optional looping video), and the browser title and search description.</p></div>
                    </div>
                    <div class="homepage-form-grid">
                        <label class="homepage-form-field"><span>Tag</span><input name="hero_eyebrow" value="{{ old('hero_eyebrow', $page->hero_eyebrow) }}" required maxlength="60"></label>
                        <label class="homepage-form-field"><span>Heading</span><input name="heading" value="{{ old('heading', $page->heading) }}" required maxlength="120"></label>
                        <label class="homepage-image-field">
                            <span>Banner image <small class="homepage-field-hint">Leave empty to keep current image</small></span>
                            <img src="{{ $page->imageUrl($page->hero_image) }}" alt="" loading="lazy">
                            <input type="file" name="hero_image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                        </label>
                        @php($heroVideo = old('hero_video', $page->hero_video))
                        <div class="homepage-video-field" data-video-field data-saved-video="{{ filled($page->hero_video) ? $page->hero_video : '' }}" data-saved-video-src="{{ filled($page->hero_video) ? $page->imageUrl($page->hero_video) : '' }}">
                            <div class="homepage-video-preview homepage-form-field-wide">
                                <video src="{{ filled($heroVideo) ? $page->imageUrl($heroVideo) : '' }}" muted loop autoplay playsinline aria-label="Banner video preview" data-video-preview @if (blank($heroVideo)) hidden @endif></video>
                                <div class="homepage-video-empty" data-video-empty @if (filled($heroVideo)) hidden @endif>
                                    <span class="homepage-video-empty-swatch" aria-hidden="true"></span>
                                    <strong>Banner image only</strong>
                                    <small>No video · the banner shows the image above</small>
                                </div>
                            </div>
                            <label class="homepage-form-field"><span>Banner video URL <small class="homepage-field-hint">Optional · plays silently behind the heading; the banner image shows while it loads</small></span>
                                <span class="homepage-input-with-action">
                                    <input name="hero_video" value="{{ $heroVideo }}" maxlength="2048" placeholder="https://…/video.mp4" data-video-url>
                                    <button type="button" class="homepage-input-clear" data-video-clear @if (blank($heroVideo)) hidden @endif>Clear <span aria-hidden="true">×</span></button>
                                </span>
                            </label>
                            <label class="homepage-form-field"><span>Or upload a video <small class="homepage-field-hint">MP4 or WebM, up to {{ intdiv(\App\Models\GroupToursPage::MAX_VIDEO_KILOBYTES, 1024) }} MB · replaces the URL</small></span><input type="file" name="hero_video_file" accept="video/mp4,video/webm" data-video-file></label>
                        </div>
                        <label class="homepage-form-field"><span>Browser title</span><input name="meta_title" value="{{ old('meta_title', $page->meta_title) }}" required maxlength="120"></label>
                        <label class="homepage-form-field homepage-form-field-wide"><span>Introduction <small class="homepage-field-hint">Optional · shown under the heading</small></span><textarea name="intro" maxlength="500">{{ old('intro', $page->intro) }}</textarea></label>
                        <label class="homepage-form-field homepage-form-field-wide"><span>Search description</span><textarea name="meta_description" required maxlength="500">{{ old('meta_description', $page->meta_description) }}</textarea></label>
                    </div>
                </section>

                <section class="homepage-form-panel">
                    <div class="homepage-form-panel-heading">
                        <span class="homepage-form-icon" aria-hidden="true">03</span>
                        <div><h2>Closing call to action</h2><p>The blue box under the cards. Its button opens WhatsApp ({{ \App\Models\GroupToursPage::WHATSAPP_NUMBER }}).</p></div>
                    </div>
                    <div class="homepage-form-grid">
                        <label class="homepage-form-field"><span>Headline</span><input name="cta_heading" value="{{ old('cta_heading', $page->cta_heading) }}" required maxlength="120"></label>
                        <label class="homepage-form-field"><span>Button label</span><input name="cta_label" value="{{ old('cta_label', $page->cta_label) }}" required maxlength="60"></label>
                        <label class="homepage-form-field homepage-form-field-wide"><span>Text</span><textarea name="cta_body" required maxlength="500">{{ old('cta_body', $page->cta_body) }}</textarea></label>
                    </div>
                </section>

                <div class="island-admin-form-actions">
                    <button class="admin-primary-button" type="submit">Save {{ $page->label() }} page <span aria-hidden="true">→</span></button>
                </div>
            </form>
        </main>
    </div>
</body>
</html>
