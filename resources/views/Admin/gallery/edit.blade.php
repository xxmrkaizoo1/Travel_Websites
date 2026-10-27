<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f4f7fc">
    <meta name="description" content="Manage the Smart Muslim gallery page content.">
    <title>Smart Muslim Gallery | Smart Holidays Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>@view-transition { navigation: auto; }</style>
</head>
<body class="admin-page">
    <a class="skip-link" href="#gallery-admin-main">Skip to gallery content</a>

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
                <a href="{{ route('gallery') }}" target="_blank" rel="noreferrer">View page ↗</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit">Sign out</button>
                </form>
            </nav>
        </header>

        <main class="homepage-editor-main" id="gallery-admin-main">
            <div class="admin-breadcrumb homepage-editor-breadcrumb">
                <a href="{{ route('admin.dashboard') }}">Workspace</a>
                <span aria-hidden="true">/</span>
                <span>Smart Muslim Tour</span>
                <span aria-hidden="true">/</span>
                <strong>Gallery</strong>
            </div>

            <section class="homepage-editor-heading">
                <div>
                    <span class="admin-eyebrow">SMART MUSLIM TOUR</span>
                    <h1>Smart Muslim gallery</h1>
                    <p>Update the banner, the destination flag cards and the photo postcards on the gallery page.</p>
                </div>
                <span class="homepage-editor-count">{{ count($page->destinations) }} destinations</span>
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

            <form class="homepage-campaign-form" data-form-slideshow method="POST" action="{{ route('admin.gallery.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <section class="homepage-form-panel">
                    <div class="homepage-form-panel-heading">
                        <span class="homepage-form-icon" aria-hidden="true">01</span>
                        <div><h2>Banner</h2><p>The title banner at the top of the gallery page.</p></div>
                    </div>
                    <div class="homepage-form-grid">
                        <label class="homepage-form-field"><span>Tag</span><input name="hero_eyebrow" value="{{ old('hero_eyebrow', $page->hero_eyebrow) }}" required maxlength="120"></label>
                        <label class="homepage-form-field"><span>Title</span><input name="hero_title" value="{{ old('hero_title', $page->hero_title) }}" required maxlength="160"></label>
                        <label class="homepage-form-field homepage-form-field-wide"><span>Text</span><textarea name="hero_body" required maxlength="500">{{ old('hero_body', $page->hero_body) }}</textarea></label>
                        <label class="homepage-image-field">
                            <span>Background image <small class="homepage-field-hint">Leave empty to keep current image</small></span>
                            <img src="{{ $page->imageUrl($page->hero_image) }}" alt="" loading="lazy">
                            <input type="file" name="hero_image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                        </label>
                    </div>
                </section>

                <section class="homepage-form-panel">
                    <div class="homepage-form-panel-heading">
                        <span class="homepage-form-icon" aria-hidden="true">02</span>
                        <div><h2>Destinations</h2><p>Each destination shows as a sliding flag card and as a photo postcard. Add up to {{ \App\Models\GalleryPage::MAX_DESTINATIONS }}; empty destinations are ignored.</p></div>
                    </div>

                    @php
                        $maxDestinations = \App\Models\GalleryPage::MAX_DESTINATIONS;
                        $destinationValues = array_values(array_filter(old('destinations', $page->destinations), 'is_array'));
                        $destinationValues = $destinationValues ?: [array_fill_keys(['country', 'flag', 'photo', 'photo_alt'], '')];
                    @endphp
                    <div class="island-gallery-editor" data-gallery-editor data-max-slides="{{ $maxDestinations }}" data-field-prefix="destinations" data-item-label="Destination" data-item-label-plural="destinations" data-gallery-paged="country" data-asset-base="{{ rtrim(asset('/'), '/') }}/" data-storage-base="{{ rtrim(\Illuminate\Support\Facades\Storage::disk('public')->url('/'), '/') }}/">
                        <div class="island-destination-fields-grid" data-gallery-slides>
                            @foreach ($destinationValues as $index => $destination)
                                <fieldset class="island-entry-panel" data-gallery-slide>
                                    <legend data-slide-legend>Destination {{ $index + 1 }}</legend>
                                    <label class="homepage-form-field"><span>Country</span><input data-gallery-field="country" name="destinations[{{ $index }}][country]" value="{{ $destination['country'] ?? '' }}" maxlength="60" @if ($loop->first) required @endif></label>
                                    <div class="gallery-destination-images">
                                    <div class="gallery-destination-image">
                                    @if (! empty($destination['flag']))
                                        <img class="tour-package-slide-preview" src="{{ $page->imageUrl($destination['flag']) }}" alt="" loading="lazy" width="160">
                                    @endif
                                    <label class="homepage-form-field"><span>Flag image URL <small class="homepage-field-hint">Or upload a file below</small></span><input data-gallery-field="flag" data-gallery-optional name="destinations[{{ $index }}][flag]" value="{{ $destination['flag'] ?? '' }}" maxlength="2048"></label>
                                    <label class="homepage-form-field"><span>Upload flag <small class="homepage-field-hint">Replaces the URL above</small></span><input type="file" data-gallery-field="flag_file" data-gallery-optional name="destinations[{{ $index }}][flag_file]" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"></label>
                                    </div>
                                    <div class="gallery-destination-image">
                                    @if (! empty($destination['photo']))
                                        <img class="tour-package-slide-preview" src="{{ $page->imageUrl($destination['photo']) }}" alt="" loading="lazy" width="160">
                                    @endif
                                    <label class="homepage-form-field"><span>Postcard photo URL <small class="homepage-field-hint">Or upload a file below</small></span><input data-gallery-field="photo" data-gallery-optional name="destinations[{{ $index }}][photo]" value="{{ $destination['photo'] ?? '' }}" maxlength="2048"></label>
                                    <label class="homepage-form-field"><span>Upload photo <small class="homepage-field-hint">Replaces the URL above</small></span><input type="file" data-gallery-field="photo_file" data-gallery-optional name="destinations[{{ $index }}][photo_file]" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"></label>
                                    </div>
                                    </div>
                                    <label class="homepage-form-field homepage-form-field-wide"><span>Photo alt text</span><input data-gallery-field="photo_alt" name="destinations[{{ $index }}][photo_alt]" value="{{ $destination['photo_alt'] ?? '' }}" maxlength="255" @if ($loop->first) required @endif></label>
                                    @include('Admin.gallery.partials.album-photos', ['photos' => $destination['photos'] ?? [], 'index' => $index])
                                    <button class="island-remove-slide" type="button" data-remove-slide aria-label="Remove destination {{ $index + 1 }}">Remove destination</button>
                                </fieldset>
                            @endforeach
                        </div>
                        <div class="island-gallery-editor-actions">
                            <span data-gallery-count aria-live="polite">{{ count($destinationValues) }} of {{ $maxDestinations }} destinations</span>
                            <button class="island-add-slide" type="button" data-add-slide>Add destination <span aria-hidden="true">＋</span></button>
                        </div>
                    </div>

                    <template data-gallery-template>
                        <fieldset class="island-entry-panel" data-gallery-slide>
                            <legend data-slide-legend></legend>
                            <label class="homepage-form-field"><span>Country</span><input data-gallery-field="country" maxlength="60"></label>
                            <div class="gallery-destination-images">
                            <div class="gallery-destination-image">
                            <label class="homepage-form-field"><span>Flag image URL <small class="homepage-field-hint">Or upload a file below</small></span><input data-gallery-field="flag" data-gallery-optional maxlength="2048"></label>
                            <label class="homepage-form-field"><span>Upload flag <small class="homepage-field-hint">Replaces the URL above</small></span><input type="file" data-gallery-field="flag_file" data-gallery-optional accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"></label>
                            </div>
                            <div class="gallery-destination-image">
                            <label class="homepage-form-field"><span>Postcard photo URL <small class="homepage-field-hint">Or upload a file below</small></span><input data-gallery-field="photo" data-gallery-optional maxlength="2048"></label>
                            <label class="homepage-form-field"><span>Upload photo <small class="homepage-field-hint">Replaces the URL above</small></span><input type="file" data-gallery-field="photo_file" data-gallery-optional accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"></label>
                            </div>
                            </div>
                            <label class="homepage-form-field homepage-form-field-wide"><span>Photo alt text</span><input data-gallery-field="photo_alt" maxlength="255"></label>
                            @include('Admin.gallery.partials.album-photos', ['photos' => [], 'index' => null])
                            <button class="island-remove-slide" type="button" data-remove-slide>Remove destination</button>
                        </fieldset>
                    </template>
                </section>

                <section class="homepage-form-panel">
                    <div class="homepage-form-panel-heading">
                        <span class="homepage-form-icon" aria-hidden="true">03</span>
                        <div><h2>Photo postcards</h2><p>The heading above the photo postcards and the labels shown on every postcard.</p></div>
                    </div>
                    <div class="homepage-form-grid">
                        <label class="homepage-form-field"><span>Tag</span><input name="moments_eyebrow" value="{{ old('moments_eyebrow', $page->moments_eyebrow) }}" required maxlength="120"></label>
                        <label class="homepage-form-field"><span>Headline</span><input name="moments_heading" value="{{ old('moments_heading', $page->moments_heading) }}" required maxlength="160"></label>
                        <label class="homepage-form-field homepage-form-field-wide"><span>Text</span><textarea name="moments_body" required maxlength="500">{{ old('moments_body', $page->moments_body) }}</textarea></label>
                        <label class="homepage-form-field"><span>Label on each photo</span><input name="moments_card_label" value="{{ old('moments_card_label', $page->moments_card_label) }}" required maxlength="60"></label>
                        <label class="homepage-form-field"><span>Small text above country name</span><input name="moments_card_eyebrow" value="{{ old('moments_card_eyebrow', $page->moments_card_eyebrow) }}" required maxlength="40"></label>
                    </div>
                </section>

                <section class="homepage-form-panel">
                    <div class="homepage-form-panel-heading">
                        <span class="homepage-form-icon" aria-hidden="true">04</span>
                        <div><h2>Page details</h2><p>The page's browser title and search description.</p></div>
                    </div>
                    <div class="homepage-form-grid">
                        <label class="homepage-form-field"><span>Browser title</span><input name="meta_title" value="{{ old('meta_title', $page->meta_title) }}" required maxlength="120"></label>
                        <label class="homepage-form-field homepage-form-field-wide"><span>Search description</span><textarea name="meta_description" required maxlength="500">{{ old('meta_description', $page->meta_description) }}</textarea></label>
                    </div>
                </section>

                <div class="island-admin-form-actions">
                    <button class="admin-primary-button" type="submit">Save gallery <span aria-hidden="true">→</span></button>
                </div>
            </form>
        </main>
    </div>

    @include('partials.gallery-album', ['eyebrow' => $page->moments_eyebrow, 'footerNote' => $page->moments_card_label])
</body>
</html>
