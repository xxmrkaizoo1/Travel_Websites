<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f4f7fc">
    <meta name="description" content="Edit an Island & Honeymoon destination spotlight.">
    <title>{{ $destination ? 'Edit '.$destination->name : 'Add destination' }} | Smart Holidays Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="admin-page">
    <a class="skip-link" href="#destination-form">Skip to destination form</a>

    <div class="homepage-editor-shell">
        <header class="homepage-editor-topbar">
            <a class="admin-brand" href="{{ route('admin.dashboard') }}">
                <span class="admin-brand-mark" aria-hidden="true">
                    <img src="{{ asset('images/smart-holidays-logo.png') }}" alt="">
                </span>
                <span><strong>SMART</strong><small>HOLIDAYS ADMIN</small></span>
            </a>
            <nav class="homepage-editor-actions" aria-label="Admin actions">
                <a href="{{ route('admin.island-honeymoon') }}">← Island &amp; Honeymoon</a>
                <a href="{{ route('island-honeymoon') }}" target="_blank" rel="noreferrer">View page ↗</a>
                <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit">Sign out</button></form>
            </nav>
        </header>

        <main class="homepage-editor-main" id="destination-form">
            <div class="admin-breadcrumb homepage-editor-breadcrumb">
                <a href="{{ route('admin.dashboard') }}">Workspace</a><span aria-hidden="true">/</span>
                <a href="{{ route('admin.island-honeymoon') }}">Island &amp; Honeymoon</a><span aria-hidden="true">/</span>
                <strong>{{ $destination ? 'Edit destination' : 'Add destination' }}</strong>
            </div>

            <section class="homepage-editor-heading">
                <div>
                    <span class="admin-eyebrow">DESTINATION SPOTLIGHT</span>
                    <h1>{{ $destination ? 'Edit '.$destination->name : 'Add a destination' }}</h1>
                    <p>Build a complete spotlight with destination facts, traveller benefits, a feature image and up to five slideshow slides.</p>
                </div>
            </section>

            @if ($errors->any())
                <div class="homepage-editor-errors" role="alert">
                    <strong>Please check the highlighted fields.</strong>
                    <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            @php
                $benefitValues = old('benefits', $benefits);
                $galleryValues = old('gallery', $gallery);
                $galleryValues = is_array($galleryValues)
                    ? array_values(array_filter($galleryValues, 'is_array'))
                    : [];
                $galleryValues = $galleryValues ?: [['image' => '', 'alt' => '', 'eyebrow' => '', 'title' => '']];
                $formAction = $destination
                    ? route('admin.island-honeymoon.destinations.update', $destination)
                    : route('admin.island-honeymoon.destinations.store');
            @endphp

            <form class="homepage-campaign-form" data-form-slideshow method="POST" action="{{ $formAction }}" enctype="multipart/form-data">
                @csrf
                @if ($destination) @method('PUT') @endif

                <section class="homepage-form-panel">
                    <div class="homepage-form-panel-heading">
                        <span class="homepage-form-icon" aria-hidden="true">01</span>
                        <div><h2>Destination story</h2><p>Share the location and the short introduction shown in its fact card.</p></div>
                    </div>
                    <div class="homepage-form-grid">
                        <label class="homepage-form-field"><span>Destination name</span><input name="name" value="{{ old('name', $destination?->name) }}" required maxlength="120"></label>
                        <label class="homepage-form-field"><span>Country</span><input name="country" value="{{ old('country', $destination?->country) }}" required maxlength="100"></label>
                        <label class="homepage-form-field"><span>Known as</span><input name="known_as" value="{{ old('known_as', $destination?->known_as) }}" required maxlength="120"></label>
                        <label class="homepage-form-field"><span>Perfect for</span><input name="perfect_for" value="{{ old('perfect_for', $destination?->perfect_for) }}" required maxlength="160"></label>
                        <label class="homepage-form-field homepage-form-field-wide"><span>Destination introduction</span><textarea name="description" required maxlength="2000">{{ old('description', $destination?->description) }}</textarea></label>
                    </div>
                </section>

                <section class="homepage-form-panel">
                    <div class="homepage-form-panel-heading">
                        <span class="homepage-form-icon" aria-hidden="true">02</span>
                        <div><h2>Why travellers love it</h2><p>Add one to five short benefits. Leave unused benefit rows empty.</p></div>
                    </div>
                    <label class="homepage-form-field island-benefits-heading-field">
                        <span>Benefits section title</span>
                        <input name="benefits_heading" value="{{ old('benefits_heading', $destination?->benefits_heading) }}" required maxlength="160">
                    </label>
                    <div class="island-destination-fields-grid">
                        @for ($index = 0; $index < 5; $index++)
                            @php($benefit = $benefitValues[$index] ?? [])
                            <fieldset class="island-entry-panel">
                                <legend>Benefit {{ $index + 1 }}{{ $index === 0 ? ' (at least one required)' : ' (optional)' }}</legend>
                                <label class="homepage-form-field"><span>Icon</span><input name="benefits[{{ $index }}][icon]" value="{{ old('benefits.'.$index.'.icon', $benefit['icon'] ?? '') }}" maxlength="12" @if ($index === 0) required @endif></label>
                                <label class="homepage-form-field"><span>Title</span><input name="benefits[{{ $index }}][title]" value="{{ old('benefits.'.$index.'.title', $benefit['title'] ?? '') }}" maxlength="120" @if ($index === 0) required @endif></label>
                                <label class="homepage-form-field"><span>Description</span><textarea name="benefits[{{ $index }}][body]" maxlength="500" @if ($index === 0) required @endif>{{ old('benefits.'.$index.'.body', $benefit['body'] ?? '') }}</textarea></label>
                            </fieldset>
                        @endfor
                    </div>
                </section>

                <section class="homepage-form-panel">
                    <div class="homepage-form-panel-heading">
                        <span class="homepage-form-icon" aria-hidden="true">03</span>
                        <div><h2>Feature image</h2><p>Choose the main destination photo and provide accessible image text.</p></div>
                    </div>
                    <div class="homepage-form-grid">
                        @if ($destination)
                            <label class="homepage-image-field homepage-form-field-wide">
                                <span>Current image</span>
                                <img src="{{ $destination->imageUrl($destination->feature_image) }}" alt="" loading="lazy">
                            </label>
                        @endif
                        <label class="homepage-form-field homepage-form-field-wide">
                            <span>{{ $destination ? 'Replace feature image (optional)' : 'Feature image' }} <small class="homepage-field-hint">JPG, PNG or WebP, up to 5 MB</small></span>
                            <input type="file" name="feature_image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" @if (! $destination) required @endif>
                            @error('feature_image') <small>{{ $message }}</small> @enderror
                        </label>
                        <label class="homepage-form-field"><span>Image alt text</span><input name="feature_image_alt" value="{{ old('feature_image_alt', $destination?->feature_image_alt) }}" required maxlength="255"></label>
                        <label class="homepage-form-field"><span>Image caption</span><input name="feature_caption" value="{{ old('feature_caption', $destination?->feature_caption) }}" required maxlength="120"></label>
                    </div>
                </section>

                <section class="homepage-form-panel">
                    <div class="homepage-form-panel-heading">
                        <span class="homepage-form-icon" aria-hidden="true">04</span>
                        <div><h2>Slideshow</h2><p>Add or remove slides. Use publicly accessible HTTP or HTTPS image URLs, up to five slides.</p></div>
                    </div>
                    <div class="island-gallery-editor" data-gallery-editor data-max-slides="5">
                        <div class="island-destination-fields-grid" data-gallery-slides>
                            @foreach ($galleryValues as $index => $slide)
                                <fieldset class="island-entry-panel" data-gallery-slide>
                                    <legend data-slide-legend>Slide {{ $index + 1 }}</legend>
                                    <label class="homepage-form-field"><span>Image URL</span><input type="url" data-gallery-field="image" name="gallery[{{ $index }}][image]" value="{{ old('gallery.'.$index.'.image', $slide['image'] ?? '') }}" maxlength="2048" @if ($loop->first) required @endif></label>
                                    <label class="homepage-form-field"><span>Image alt text</span><input data-gallery-field="alt" name="gallery[{{ $index }}][alt]" value="{{ old('gallery.'.$index.'.alt', $slide['alt'] ?? '') }}" maxlength="255" @if ($loop->first) required @endif></label>
                                    <label class="homepage-form-field"><span>Eyebrow</span><input data-gallery-field="eyebrow" name="gallery[{{ $index }}][eyebrow]" value="{{ old('gallery.'.$index.'.eyebrow', $slide['eyebrow'] ?? '') }}" maxlength="120" @if ($loop->first) required @endif></label>
                                    <label class="homepage-form-field"><span>Slide title</span><input data-gallery-field="title" name="gallery[{{ $index }}][title]" value="{{ old('gallery.'.$index.'.title', $slide['title'] ?? '') }}" maxlength="120" @if ($loop->first) required @endif></label>
                                    <button class="island-remove-slide" type="button" data-remove-slide aria-label="Remove slide {{ $index + 1 }}">Remove slide</button>
                                </fieldset>
                            @endforeach
                        </div>
                        <div class="island-gallery-editor-actions">
                            <span data-gallery-count aria-live="polite">{{ count($galleryValues) }} of 5 slides</span>
                            <button class="island-add-slide" type="button" data-add-slide>Add slide <span aria-hidden="true">＋</span></button>
                        </div>
                    </div>

                    <template data-gallery-template>
                        <fieldset class="island-entry-panel" data-gallery-slide>
                            <legend data-slide-legend></legend>
                            <label class="homepage-form-field"><span>Image URL</span><input type="url" data-gallery-field="image" maxlength="2048"></label>
                            <label class="homepage-form-field"><span>Image alt text</span><input data-gallery-field="alt" maxlength="255"></label>
                            <label class="homepage-form-field"><span>Eyebrow</span><input data-gallery-field="eyebrow" maxlength="120"></label>
                            <label class="homepage-form-field"><span>Slide title</span><input data-gallery-field="title" maxlength="120"></label>
                            <button class="island-remove-slide" type="button" data-remove-slide>Remove slide</button>
                        </fieldset>
                    </template>
                </section>

                <div class="island-admin-form-actions">
                    <a class="homepage-editor-back" href="{{ route('admin.island-honeymoon') }}">Cancel</a>
                    <button class="admin-primary-button" type="submit">{{ $destination ? 'Save destination' : 'Add destination spotlight' }} <span aria-hidden="true">→</span></button>
                </div>
            </form>
        </main>

        <footer class="homepage-editor-footer">Smart Holidays · Island &amp; Honeymoon content manager</footer>
    </div>
</body>
</html>
