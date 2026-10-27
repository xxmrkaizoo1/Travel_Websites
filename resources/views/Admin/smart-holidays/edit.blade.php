<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f4f7fc">
    <meta name="description" content="Manage the Smart Holidays homepage content.">
    <title>Smart Holidays Homepage | Smart Holidays Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>@view-transition { navigation: auto; }</style>
</head>
<body class="admin-page">
    <a class="skip-link" href="#smart-holidays-admin-main">Skip to Smart Holidays homepage content</a>

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
                <a href="{{ route('home') }}" target="_blank" rel="noreferrer">View page ↗</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit">Sign out</button>
                </form>
            </nav>
        </header>

        <main class="homepage-editor-main" id="smart-holidays-admin-main">
            <div class="admin-breadcrumb homepage-editor-breadcrumb">
                <a href="{{ route('admin.dashboard') }}">Workspace</a>
                <span aria-hidden="true">/</span>
                <span>Smart Holidays</span>
                <span aria-hidden="true">/</span>
                <strong>Homepage</strong>
            </div>

            <section class="homepage-editor-heading">
                <div>
                    <span class="admin-eyebrow">SMART HOLIDAYS</span>
                    <h1>Smart Holidays homepage</h1>
                    <p>Update the text, images and links on the main Smart Holidays homepage.</p>
                </div>
                <span class="homepage-editor-count">{{ count($page->journey_slides) }} featured slides</span>
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

            <form class="homepage-campaign-form" data-form-slideshow method="POST" action="{{ route('admin.smart-holidays.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <section class="homepage-form-panel">
                    <div class="homepage-form-panel-heading">
                        <span class="homepage-form-icon" aria-hidden="true">01</span>
                        <div><h2>Hero section</h2><p>The welcome banner at the top of the homepage.</p></div>
                    </div>
                    <div class="homepage-form-grid">
                        <label class="homepage-form-field"><span>Eyebrow</span><input name="hero_eyebrow" value="{{ old('hero_eyebrow', $page->hero_eyebrow) }}" required maxlength="120"></label>
                        <label class="homepage-form-field"><span>Headline</span><input name="hero_title" value="{{ old('hero_title', $page->hero_title) }}" required maxlength="160"></label>
                        <label class="homepage-form-field homepage-form-field-wide"><span>Introductory text</span><textarea name="hero_body" required maxlength="1500">{{ old('hero_body', $page->hero_body) }}</textarea></label>
                        <label class="homepage-form-field"><span>WhatsApp button label</span><input name="hero_cta_label" value="{{ old('hero_cta_label', $page->hero_cta_label) }}" required maxlength="60"></label>
                        <label class="homepage-form-field"><span>Explore button label</span><input name="hero_secondary_label" value="{{ old('hero_secondary_label', $page->hero_secondary_label) }}" required maxlength="60"></label>
                        <label class="homepage-form-field"><span>Banner alt text</span><input name="hero_image_alt" value="{{ old('hero_image_alt', $page->hero_image_alt) }}" required maxlength="255"></label>
                        <label class="homepage-image-field">
                            <span>Banner image <small class="homepage-field-hint">Leave empty to keep current image</small></span>
                            <img src="{{ $page->imageUrl($page->hero_image) }}" alt="" loading="lazy">
                            <input type="file" name="hero_image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                        </label>
                    </div>
                </section>

                <section class="homepage-form-panel">
                    <div class="homepage-form-panel-heading">
                        <span class="homepage-form-icon" aria-hidden="true">02</span>
                        <div><h2>Banner highlights</h2><p>The four short labels shown along the bottom of the banner.</p></div>
                    </div>
                    <div class="homepage-form-grid">
                        @foreach ($page->stats as $index => $stat)
                            <label class="homepage-form-field"><span>Highlight {{ $index + 1 }} title</span><input name="stats[{{ $index }}][title]" value="{{ old("stats.$index.title", $stat['title']) }}" required maxlength="40"></label>
                            <label class="homepage-form-field"><span>Highlight {{ $index + 1 }} text</span><input name="stats[{{ $index }}][body]" value="{{ old("stats.$index.body", $stat['body']) }}" required maxlength="80"></label>
                        @endforeach
                    </div>
                </section>

                <section class="homepage-form-panel">
                    <div class="homepage-form-panel-heading">
                        <span class="homepage-form-icon" aria-hidden="true">03</span>
                        <div><h2>What we do</h2><p>The four service cards. Each card keeps its icon.</p></div>
                    </div>
                    <div class="homepage-form-grid">
                        <label class="homepage-form-field"><span>Eyebrow</span><input name="services_eyebrow" value="{{ old('services_eyebrow', $page->services_eyebrow) }}" required maxlength="120"></label>
                        <label class="homepage-form-field"><span>Headline</span><input name="services_heading" value="{{ old('services_heading', $page->services_heading) }}" required maxlength="160"></label>
                        @foreach ($page->services as $index => $service)
                            <label class="homepage-form-field"><span>Card {{ $index + 1 }} title</span><input name="services[{{ $index }}][title]" value="{{ old("services.$index.title", $service['title']) }}" required maxlength="80"></label>
                            <label class="homepage-form-field"><span>Card {{ $index + 1 }} text</span><textarea name="services[{{ $index }}][body]" required maxlength="300">{{ old("services.$index.body", $service['body']) }}</textarea></label>
                        @endforeach
                    </div>
                </section>

                <section class="homepage-form-panel">
                    <div class="homepage-form-panel-heading">
                        <span class="homepage-form-icon" aria-hidden="true">04</span>
                        <div><h2>Featured destinations carousel</h2><p>Large slides with thumbnails. Add up to {{ \App\Models\SmartHolidaysPage::MAX_JOURNEY_SLIDES }} slides; empty slides are ignored.</p></div>
                    </div>

                    @php
                        $maxSlides = \App\Models\SmartHolidaysPage::MAX_JOURNEY_SLIDES;
                        $slideValues = array_values(array_filter(old('journey_slides', $page->journey_slides), 'is_array'));
                        $slideValues = $slideValues ?: [array_fill_keys(['image', 'alt', 'eyebrow', 'title', 'body', 'link_label', 'link_url', 'thumb'], '')];
                    @endphp
                    <div class="island-gallery-editor" data-gallery-editor data-max-slides="{{ $maxSlides }}" data-field-prefix="journey_slides" data-gallery-paged="title" data-gallery-columns>
                        <div class="island-destination-fields-grid" data-gallery-slides>
                            @foreach ($slideValues as $index => $slide)
                                <fieldset class="island-entry-panel" data-gallery-slide>
                                    <legend data-slide-legend>Slide {{ $index + 1 }}</legend>
                                    @if (! empty($slide['image']))
                                        <img class="tour-package-slide-preview" src="{{ $page->imageUrl($slide['image']) }}" alt="" loading="lazy" width="160">
                                    @endif
                                    @if (! empty($slide['video']))
                                        <video class="tour-package-slide-preview" src="{{ $page->imageUrl($slide['video']) }}" muted loop autoplay playsinline width="160" aria-label="Slide {{ $index + 1 }} video preview"></video>
                                    @endif
                                    <label class="homepage-form-field"><span>Image URL <small class="homepage-field-hint">Or upload a file below</small></span><input data-gallery-field="image" data-gallery-optional name="journey_slides[{{ $index }}][image]" value="{{ $slide['image'] ?? '' }}" maxlength="2048"></label>
                                    <label class="homepage-form-field"><span>Upload image <small class="homepage-field-hint">Replaces the URL above</small></span><input type="file" data-gallery-field="image_file" data-gallery-optional name="journey_slides[{{ $index }}][image_file]" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"></label>
                                    <label class="homepage-form-field"><span>Video URL <small class="homepage-field-hint">Optional · plays silently behind the slide; the image shows while it loads. Empty it to remove</small></span><input data-gallery-field="video" data-gallery-optional name="journey_slides[{{ $index }}][video]" value="{{ $slide['video'] ?? '' }}" maxlength="2048" placeholder="https://…/video.mp4"></label>
                                    <label class="homepage-form-field"><span>Upload video <small class="homepage-field-hint">MP4 or WebM, up to {{ intdiv(\App\Models\SmartHolidaysPage::MAX_VIDEO_KILOBYTES, 1024) }} MB · replaces the video URL</small></span><input type="file" data-gallery-field="video_file" data-gallery-optional name="journey_slides[{{ $index }}][video_file]" accept="video/mp4,video/webm"></label>
                                    <label class="homepage-form-field"><span>Image alt text</span><input data-gallery-field="alt" name="journey_slides[{{ $index }}][alt]" value="{{ $slide['alt'] ?? '' }}" maxlength="255" @if ($loop->first) required @endif></label>
                                    <label class="homepage-form-field"><span>Eyebrow</span><input data-gallery-field="eyebrow" name="journey_slides[{{ $index }}][eyebrow]" value="{{ $slide['eyebrow'] ?? '' }}" maxlength="120" @if ($loop->first) required @endif></label>
                                    <label class="homepage-form-field"><span>Slide title</span><input data-gallery-field="title" name="journey_slides[{{ $index }}][title]" value="{{ $slide['title'] ?? '' }}" maxlength="120" @if ($loop->first) required @endif></label>
                                    <label class="homepage-form-field"><span>Thumbnail label</span><input data-gallery-field="thumb" name="journey_slides[{{ $index }}][thumb]" value="{{ $slide['thumb'] ?? '' }}" maxlength="40" @if ($loop->first) required @endif></label>
                                    <label class="homepage-form-field homepage-form-field-wide"><span>Slide text</span><textarea data-gallery-field="body" name="journey_slides[{{ $index }}][body]" maxlength="500" @if ($loop->first) required @endif>{{ $slide['body'] ?? '' }}</textarea></label>
                                    <label class="homepage-form-field"><span>Link label</span><input data-gallery-field="link_label" name="journey_slides[{{ $index }}][link_label]" value="{{ $slide['link_label'] ?? '' }}" maxlength="60" @if ($loop->first) required @endif></label>
                                    <label class="homepage-form-field"><span>Link URL <small class="homepage-field-hint">e.g. /vietnam or https://…</small></span><input data-gallery-field="link_url" name="journey_slides[{{ $index }}][link_url]" value="{{ $slide['link_url'] ?? '' }}" maxlength="2048" @if ($loop->first) required @endif></label>
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
                            <label class="homepage-form-field"><span>Video URL <small class="homepage-field-hint">Optional · plays silently behind the slide; the image shows while it loads. Empty it to remove</small></span><input data-gallery-field="video" data-gallery-optional maxlength="2048" placeholder="https://…/video.mp4"></label>
                            <label class="homepage-form-field"><span>Upload video <small class="homepage-field-hint">MP4 or WebM, up to {{ intdiv(\App\Models\SmartHolidaysPage::MAX_VIDEO_KILOBYTES, 1024) }} MB · replaces the video URL</small></span><input type="file" data-gallery-field="video_file" data-gallery-optional accept="video/mp4,video/webm"></label>
                            <label class="homepage-form-field"><span>Image alt text</span><input data-gallery-field="alt" maxlength="255"></label>
                            <label class="homepage-form-field"><span>Eyebrow</span><input data-gallery-field="eyebrow" maxlength="120"></label>
                            <label class="homepage-form-field"><span>Slide title</span><input data-gallery-field="title" maxlength="120"></label>
                            <label class="homepage-form-field"><span>Thumbnail label</span><input data-gallery-field="thumb" maxlength="40"></label>
                            <label class="homepage-form-field homepage-form-field-wide"><span>Slide text</span><textarea data-gallery-field="body" maxlength="500"></textarea></label>
                            <label class="homepage-form-field"><span>Link label</span><input data-gallery-field="link_label" maxlength="60"></label>
                            <label class="homepage-form-field"><span>Link URL <small class="homepage-field-hint">e.g. /vietnam or https://…</small></span><input data-gallery-field="link_url" maxlength="2048"></label>
                            <button class="island-remove-slide" type="button" data-remove-slide>Remove slide</button>
                        </fieldset>
                    </template>
                </section>

                <section class="homepage-form-panel">
                    <div class="homepage-form-panel-heading">
                        <span class="homepage-form-icon" aria-hidden="true">05</span>
                        <div><h2>Section headings</h2><p>Headings for the popular tours and brands sections. Popular tour cards come from the Series Departures 2026 page.</p></div>
                    </div>
                    <div class="homepage-form-grid">
                        <label class="homepage-form-field"><span>Popular tours eyebrow</span><input name="popular_eyebrow" value="{{ old('popular_eyebrow', $page->popular_eyebrow) }}" required maxlength="120"></label>
                        <label class="homepage-form-field"><span>Popular tours headline</span><input name="popular_heading" value="{{ old('popular_heading', $page->popular_heading) }}" required maxlength="160"></label>
                        <label class="homepage-form-field homepage-form-field-wide"><span>Popular tours text</span><input name="popular_body" value="{{ old('popular_body', $page->popular_body) }}" required maxlength="500"></label>
                        @php($popularVideo = old('popular_video', $page->popular_video))
                        <div class="homepage-video-field" data-video-field data-saved-video="{{ filled($page->popular_video) ? $page->popular_video : '' }}" data-saved-video-src="{{ filled($page->popular_video) ? $page->imageUrl($page->popular_video) : '' }}">
                            <div class="homepage-video-preview homepage-form-field-wide">
                                <video src="{{ filled($popularVideo) ? $page->imageUrl($popularVideo) : '' }}" muted loop autoplay playsinline aria-label="Popular tours background video preview" data-video-preview @if (blank($popularVideo)) hidden @endif></video>
                                <div class="homepage-video-empty" data-video-empty @if (filled($popularVideo)) hidden @endif>
                                    <span class="homepage-video-empty-swatch" aria-hidden="true"></span>
                                    <strong>Default background</strong>
                                    <small>No video · the section shows its plain white background</small>
                                </div>
                            </div>
                            <label class="homepage-form-field"><span>Popular tours background video URL <small class="homepage-field-hint">Optional · loops silently behind the tour cards</small></span>
                                <span class="homepage-input-with-action">
                                    <input name="popular_video" value="{{ $popularVideo }}" maxlength="2048" placeholder="https://…/video.mp4" data-video-url>
                                    <button type="button" class="homepage-input-clear" data-video-clear @if (blank($popularVideo)) hidden @endif>Clear <span aria-hidden="true">×</span></button>
                                </span>
                            </label>
                            <label class="homepage-form-field"><span>Or upload a video <small class="homepage-field-hint">MP4 or WebM, up to {{ intdiv(\App\Models\SmartHolidaysPage::MAX_VIDEO_KILOBYTES, 1024) }} MB · replaces the URL</small></span><input type="file" name="popular_video_file" accept="video/mp4,video/webm" data-video-file></label>
                        </div>
                        <label class="homepage-form-field"><span>Brands eyebrow</span><input name="brands_eyebrow" value="{{ old('brands_eyebrow', $page->brands_eyebrow) }}" required maxlength="120"></label>
                        <label class="homepage-form-field"><span>Brands headline</span><input name="brands_heading" value="{{ old('brands_heading', $page->brands_heading) }}" required maxlength="160"></label>
                    </div>
                </section>

                <section class="homepage-form-panel">
                    <div class="homepage-form-panel-heading">
                        <span class="homepage-form-icon" aria-hidden="true">06</span>
                        <div><h2>Call to action &amp; page details</h2><p>The closing WhatsApp section and the page's browser title and description.</p></div>
                    </div>
                    <div class="homepage-form-grid">
                        <label class="homepage-form-field"><span>Headline</span><input name="cta_heading" value="{{ old('cta_heading', $page->cta_heading) }}" required maxlength="160"></label>
                        <label class="homepage-form-field"><span>Button label</span><input name="cta_label" value="{{ old('cta_label', $page->cta_label) }}" required maxlength="60"></label>
                        <label class="homepage-form-field homepage-form-field-wide"><span>Text</span><input name="cta_body" value="{{ old('cta_body', $page->cta_body) }}" required maxlength="500"></label>
                        <label class="homepage-form-field"><span>WhatsApp number <small class="homepage-field-hint">Digits only, with country code (e.g. 60165500590)</small></span><input name="whatsapp_number" value="{{ old('whatsapp_number', $page->whatsapp_number) }}" required maxlength="20" inputmode="numeric"></label>
                        <label class="homepage-form-field"><span>Browser title</span><input name="meta_title" value="{{ old('meta_title', $page->meta_title) }}" required maxlength="120"></label>
                        <label class="homepage-form-field homepage-form-field-wide"><span>Search description</span><textarea name="meta_description" required maxlength="500">{{ old('meta_description', $page->meta_description) }}</textarea></label>
                    </div>
                </section>

                <div class="island-admin-form-actions">
                    <button class="admin-primary-button" type="submit">Save homepage <span aria-hidden="true">→</span></button>
                </div>
            </form>
        </main>
    </div>
</body>
</html>
