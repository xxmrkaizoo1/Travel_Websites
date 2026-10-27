<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f4f7fc">
    <meta name="description" content="Manage the Smart Muslim private tours introduction page.">
    <title>Private Tours | Smart Holidays Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>@view-transition { navigation: auto; }</style>
</head>
<body class="admin-page">
    <a class="skip-link" href="#private-tours-admin-main">Skip to private tours content</a>

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
                <a href="{{ route('tour-package-private') }}" target="_blank" rel="noreferrer">View page ↗</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit">Sign out</button>
                </form>
            </nav>
        </header>

        <main class="homepage-editor-main" id="private-tours-admin-main">
            <div class="admin-breadcrumb homepage-editor-breadcrumb">
                <a href="{{ route('admin.dashboard') }}">Workspace</a>
                <span aria-hidden="true">/</span>
                <span>Smart Muslim</span>
                <span aria-hidden="true">/</span>
                <span>Tour Package (Private)</span>
                <span aria-hidden="true">/</span>
                <strong>Introduction</strong>
            </div>

            <section class="homepage-editor-heading">
                <div>
                    <span class="admin-eyebrow">SMART MUSLIM</span>
                    <h1>Private tours introduction</h1>
                    <p>Update the banner, highlights, introduction, destination cards and closing box on the Tour Package (Private) page.</p>
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

            <form class="homepage-campaign-form" data-form-slideshow method="POST" action="{{ route('admin.private-tours.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <section class="homepage-form-panel">
                    <div class="homepage-form-panel-heading">
                        <span class="homepage-form-icon" aria-hidden="true">01</span>
                        <div><h2>Banner</h2><p>The photo banner at the top of the page. The second heading line shows in a green gradient. Clear the image to go back to the default temple photo.</p></div>
                    </div>
                    <div class="homepage-form-grid">
                        <label class="homepage-form-field"><span>Tag</span><input name="hero_eyebrow" value="{{ old('hero_eyebrow', $page->hero_eyebrow) }}" required maxlength="60"></label>
                        <label class="homepage-form-field"><span>Corner note <small class="homepage-field-hint">Optional · small text at the bottom right</small></span><input name="hero_note" value="{{ old('hero_note', $page->hero_note) }}" maxlength="80"></label>
                        <label class="homepage-form-field"><span>Heading</span><input name="hero_title" value="{{ old('hero_title', $page->hero_title) }}" required maxlength="80"></label>
                        <label class="homepage-form-field"><span>Heading, second line</span><input name="hero_highlight" value="{{ old('hero_highlight', $page->hero_highlight) }}" required maxlength="80"></label>
                        <label class="homepage-form-field homepage-form-field-wide"><span>Text</span><textarea name="hero_body" required maxlength="500">{{ old('hero_body', $page->hero_body) }}</textarea></label>
                        <label class="homepage-form-field"><span>Button label <small class="homepage-field-hint">Scrolls to the destinations</small></span><input name="hero_cta_label" value="{{ old('hero_cta_label', $page->hero_cta_label) }}" required maxlength="40"></label>
                        @php $heroImage = old('hero_image', $page->hero_image); @endphp
                        @include('Admin.partials.image-clear-field', [
                            'value' => (string) $heroImage,
                            'previewSrc' => filled($heroImage) ? $page->imageUrl($heroImage) : '',
                            'urlAttributes' => 'name="hero_image"',
                            'fileAttributes' => 'name="hero_image_file"',
                            'defaultTitle' => 'Default banner photo',
                            'defaultHint' => 'The temple photo shows until you add your own',
                        ])
                    </div>
                </section>

                <section class="homepage-form-panel">
                    <div class="homepage-form-panel-heading">
                        <span class="homepage-form-icon" aria-hidden="true">02</span>
                        <div><h2>Highlights</h2><p>The white strip under the banner. Up to {{ \App\Models\PrivateToursPage::MAX_PERKS }} highlights; remove them all to hide the strip. The icon can be any emoji.</p></div>
                    </div>

                    @php
                        $maxPerks = \App\Models\PrivateToursPage::MAX_PERKS;
                        $perkValues = array_values(array_filter(old('perks', $page->perks), 'is_array'));
                    @endphp
                    <div class="island-gallery-editor" data-gallery-editor data-max-slides="{{ $maxPerks }}" data-field-prefix="perks" data-item-label="Highlight" data-item-label-plural="highlights" data-gallery-columns>
                        <div class="island-destination-fields-grid" data-gallery-slides>
                            @foreach ($perkValues as $index => $perk)
                                <fieldset class="island-entry-panel" data-gallery-slide>
                                    <legend data-slide-legend>Highlight {{ $index + 1 }}</legend>
                                    <label class="homepage-form-field"><span>Icon <small class="homepage-field-hint">Optional · one emoji</small></span><input data-gallery-field="icon" data-gallery-optional name="perks[{{ $index }}][icon]" value="{{ $perk['icon'] ?? '' }}" maxlength="8"></label>
                                    <label class="homepage-form-field"><span>Title</span><input data-gallery-field="title" data-gallery-optional name="perks[{{ $index }}][title]" value="{{ $perk['title'] ?? '' }}" maxlength="60"></label>
                                    <label class="homepage-form-field homepage-form-field-wide"><span>Text <small class="homepage-field-hint">Optional</small></span><input data-gallery-field="body" data-gallery-optional name="perks[{{ $index }}][body]" value="{{ $perk['body'] ?? '' }}" maxlength="160"></label>
                                    <button class="island-remove-slide" type="button" data-remove-slide aria-label="Remove highlight {{ $index + 1 }}">Remove highlight</button>
                                </fieldset>
                            @endforeach
                        </div>
                        <div class="island-gallery-editor-actions">
                            <span data-gallery-count aria-live="polite">{{ count($perkValues) }} of {{ $maxPerks }} highlights</span>
                            <button class="island-add-slide" type="button" data-add-slide>Add highlight <span aria-hidden="true">＋</span></button>
                        </div>
                    </div>

                    <template data-gallery-template>
                        <fieldset class="island-entry-panel" data-gallery-slide>
                            <legend data-slide-legend></legend>
                            <label class="homepage-form-field"><span>Icon <small class="homepage-field-hint">Optional · one emoji</small></span><input data-gallery-field="icon" data-gallery-optional maxlength="8"></label>
                            <label class="homepage-form-field"><span>Title</span><input data-gallery-field="title" data-gallery-optional maxlength="60"></label>
                            <label class="homepage-form-field homepage-form-field-wide"><span>Text <small class="homepage-field-hint">Optional</small></span><input data-gallery-field="body" data-gallery-optional maxlength="160"></label>
                            <button class="island-remove-slide" type="button" data-remove-slide>Remove highlight</button>
                        </fieldset>
                    </template>
                </section>

                <section class="homepage-form-panel">
                    <div class="homepage-form-panel-heading">
                        <span class="homepage-form-icon" aria-hidden="true">03</span>
                        <div><h2>Introduction</h2><p>The text and photo under the highlights. The highlighted word shows in green after the heading.</p></div>
                    </div>
                    <div class="homepage-form-grid">
                        <label class="homepage-form-field"><span>Label</span><input name="intro_label" value="{{ old('intro_label', $page->intro_label) }}" required maxlength="60"></label>
                        <label class="homepage-form-field"><span>Link label <small class="homepage-field-hint">Scrolls to the destinations</small></span><input name="intro_link_label" value="{{ old('intro_link_label', $page->intro_link_label) }}" required maxlength="40"></label>
                        <label class="homepage-form-field"><span>Heading</span><input name="intro_heading" value="{{ old('intro_heading', $page->intro_heading) }}" required maxlength="120"></label>
                        <label class="homepage-form-field"><span>Highlighted word <small class="homepage-field-hint">Optional</small></span><input name="intro_highlight" value="{{ old('intro_highlight', $page->intro_highlight) }}" maxlength="40"></label>
                        <label class="homepage-form-field homepage-form-field-wide"><span>Text</span><textarea name="intro_body" required maxlength="1000">{{ old('intro_body', $page->intro_body) }}</textarea></label>
                        @php $introImage = old('intro_image', $page->intro_image); @endphp
                        @include('Admin.partials.image-clear-field', [
                            'value' => (string) $introImage,
                            'previewSrc' => filled($introImage) ? $page->imageUrl($introImage) : '',
                            'urlAttributes' => 'name="intro_image"',
                            'fileAttributes' => 'name="intro_image_file"',
                            'defaultTitle' => 'Default introduction photo',
                            'defaultHint' => 'The Bali temple photo shows until you add your own',
                        ])
                        <label class="homepage-form-field"><span>Photo alt text <small class="homepage-field-hint">Optional</small></span><input name="intro_image_alt" value="{{ old('intro_image_alt', $page->intro_image_alt) }}" maxlength="255"></label>
                        <label class="homepage-form-field"><span>Photo caption <small class="homepage-field-hint">Optional · the white tag on the photo</small></span><input name="intro_caption" value="{{ old('intro_caption', $page->intro_caption) }}" maxlength="80"></label>
                    </div>
                </section>

                <section class="homepage-form-panel">
                    <div class="homepage-form-panel-heading">
                        <span class="homepage-form-icon" aria-hidden="true">04</span>
                        <div><h2>Destinations</h2><p>The photo cards. The first card is shown wider. Each card links to a page, for example /thailand. Add up to {{ \App\Models\PrivateToursPage::MAX_DESTINATIONS }}; empty cards are ignored.</p></div>
                    </div>
                    <div class="homepage-form-grid">
                        <label class="homepage-form-field"><span>Label</span><input name="destinations_label" value="{{ old('destinations_label', $page->destinations_label) }}" required maxlength="60"></label>
                        <label class="homepage-form-field"><span>Heading</span><input name="destinations_heading" value="{{ old('destinations_heading', $page->destinations_heading) }}" required maxlength="120"></label>
                        <label class="homepage-form-field homepage-form-field-wide"><span>Text <small class="homepage-field-hint">Optional</small></span><textarea name="destinations_body" maxlength="500">{{ old('destinations_body', $page->destinations_body) }}</textarea></label>
                    </div>

                    @php
                        $maxDestinations = \App\Models\PrivateToursPage::MAX_DESTINATIONS;
                        $destinationValues = array_values(array_filter(old('destinations', $page->destinations), 'is_array'));
                        $destinationValues = $destinationValues ?: [array_fill_keys(['name', 'tagline', 'image', 'alt', 'link_url', 'link_label'], '')];
                    @endphp
                    <div class="island-gallery-editor" data-gallery-editor data-max-slides="{{ $maxDestinations }}" data-field-prefix="destinations" data-item-label="Destination" data-item-label-plural="destinations" data-gallery-paged="name" data-gallery-columns>
                        <div class="island-destination-fields-grid" data-gallery-slides>
                            @foreach ($destinationValues as $index => $destination)
                                <fieldset class="island-entry-panel" data-gallery-slide>
                                    <legend data-slide-legend>Destination {{ $index + 1 }}</legend>
                                    @if (! empty($destination['image']))
                                        <img class="tour-package-slide-preview" src="{{ $page->imageUrl($destination['image']) }}" alt="" loading="lazy" width="160">
                                    @endif
                                    <label class="homepage-form-field"><span>Name</span><input data-gallery-field="name" name="destinations[{{ $index }}][name]" value="{{ $destination['name'] ?? '' }}" maxlength="40" @if ($loop->first) required @endif></label>
                                    <label class="homepage-form-field"><span>Link <small class="homepage-field-hint">A site path such as /thailand, or a full URL</small></span><input data-gallery-field="link_url" name="destinations[{{ $index }}][link_url]" value="{{ $destination['link_url'] ?? '' }}" maxlength="2048" @if ($loop->first) required @endif></label>
                                    <label class="homepage-form-field homepage-form-field-wide"><span>Short description <small class="homepage-field-hint">Optional</small></span><input data-gallery-field="tagline" data-gallery-optional name="destinations[{{ $index }}][tagline]" value="{{ $destination['tagline'] ?? '' }}" maxlength="160"></label>
                                    <label class="homepage-form-field"><span>Button label <small class="homepage-field-hint">Optional · defaults to "Explore"</small></span><input data-gallery-field="link_label" data-gallery-optional name="destinations[{{ $index }}][link_label]" value="{{ $destination['link_label'] ?? '' }}" maxlength="40"></label>
                                    <label class="homepage-form-field"><span>Image URL <small class="homepage-field-hint">Or upload a file</small></span><input data-gallery-field="image" data-gallery-optional name="destinations[{{ $index }}][image]" value="{{ $destination['image'] ?? '' }}" maxlength="2048"></label>
                                    <label class="homepage-form-field"><span>Upload image <small class="homepage-field-hint">Replaces the URL</small></span><input type="file" data-gallery-field="image_file" data-gallery-optional name="destinations[{{ $index }}][image_file]" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"></label>
                                    <label class="homepage-form-field"><span>Image alt text</span><input data-gallery-field="alt" name="destinations[{{ $index }}][alt]" value="{{ $destination['alt'] ?? '' }}" maxlength="255" @if ($loop->first) required @endif></label>
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
                            <label class="homepage-form-field"><span>Name</span><input data-gallery-field="name" maxlength="40"></label>
                            <label class="homepage-form-field"><span>Link <small class="homepage-field-hint">A site path such as /thailand, or a full URL</small></span><input data-gallery-field="link_url" maxlength="2048"></label>
                            <label class="homepage-form-field homepage-form-field-wide"><span>Short description <small class="homepage-field-hint">Optional</small></span><input data-gallery-field="tagline" data-gallery-optional maxlength="160"></label>
                            <label class="homepage-form-field"><span>Button label <small class="homepage-field-hint">Optional · defaults to "Explore"</small></span><input data-gallery-field="link_label" data-gallery-optional maxlength="40"></label>
                            <label class="homepage-form-field"><span>Image URL <small class="homepage-field-hint">Or upload a file</small></span><input data-gallery-field="image" data-gallery-optional maxlength="2048"></label>
                            <label class="homepage-form-field"><span>Upload image <small class="homepage-field-hint">Replaces the URL</small></span><input type="file" data-gallery-field="image_file" data-gallery-optional accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"></label>
                            <label class="homepage-form-field"><span>Image alt text</span><input data-gallery-field="alt" maxlength="255"></label>
                            <button class="island-remove-slide" type="button" data-remove-slide>Remove destination</button>
                        </fieldset>
                    </template>
                </section>

                <section class="homepage-form-panel">
                    <div class="homepage-form-panel-heading">
                        <span class="homepage-form-icon" aria-hidden="true">05</span>
                        <div><h2>Closing box &amp; page details</h2><p>The green box at the end of the page; its button opens WhatsApp ({{ \App\Models\PrivateToursPage::WHATSAPP_NUMBER }}) and is also the second banner button. Plus the browser title and search description.</p></div>
                    </div>
                    <div class="homepage-form-grid">
                        <label class="homepage-form-field"><span>Label</span><input name="contact_label" value="{{ old('contact_label', $page->contact_label) }}" required maxlength="60"></label>
                        <label class="homepage-form-field"><span>Button label</span><input name="contact_cta_label" value="{{ old('contact_cta_label', $page->contact_cta_label) }}" required maxlength="40"></label>
                        <label class="homepage-form-field homepage-form-field-wide"><span>Headline</span><input name="contact_heading" value="{{ old('contact_heading', $page->contact_heading) }}" required maxlength="120"></label>
                        <label class="homepage-form-field homepage-form-field-wide"><span>Text</span><textarea name="contact_body" required maxlength="500">{{ old('contact_body', $page->contact_body) }}</textarea></label>
                        <label class="homepage-form-field homepage-form-field-wide"><span>Browser title</span><input name="meta_title" value="{{ old('meta_title', $page->meta_title) }}" required maxlength="120"></label>
                        <label class="homepage-form-field homepage-form-field-wide"><span>Search description</span><textarea name="meta_description" required maxlength="500">{{ old('meta_description', $page->meta_description) }}</textarea></label>
                    </div>
                </section>

                <div class="island-admin-form-actions">
                    <button class="admin-primary-button" type="submit">Save private tours page <span aria-hidden="true">→</span></button>
                </div>
            </form>
        </main>
    </div>
</body>
</html>
