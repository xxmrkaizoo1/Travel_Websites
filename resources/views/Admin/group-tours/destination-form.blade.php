<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f4f7fc">
    <meta name="description" content="Manage a Smart Holidays group tour destination page.">
    <title>{{ $destination ? $destination->name : 'New destination' }} | Group Tours | Smart Holidays Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('Admin.partials.theme-head')
    <style>@view-transition { navigation: auto; }</style>
</head>
<body class="admin-page">
    <a class="skip-link" href="#group-tours-admin-main">Skip to destination content</a>

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
                @if ($destination)
                    <a href="{{ route('group-tours.destination', $destination) }}" target="_blank" rel="noreferrer">View page ↗</a>
                @endif
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
                <a href="{{ route('admin.group-tours.edit') }}">Group Tours</a>
                <span aria-hidden="true">/</span>
                <strong>{{ $destination ? $destination->name : 'New destination' }}</strong>
            </div>

            <section class="homepage-editor-heading">
                <div>
                    <span class="admin-eyebrow">GROUP TOURS · DESTINATION PAGE</span>
                    <h1>{{ $destination ? $destination->name : 'New destination page' }}</h1>
                    <p>{{ $destination ? 'Edit the banner, regions and '.$destination->placeTerm(2).' shown on this page. Each card has an Enquire button that opens WhatsApp.' : 'Create a page such as Malaysia or Vietnam that lists its places by region. Link it from a tour card on the Tour Destinations page.' }}</p>
                </div>
                @if ($destination)
                    <span class="homepage-editor-count">{{ count($destination->states ?? []) }} {{ $destination->placeTerm(count($destination->states ?? [])) }}</span>
                @endif
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

            @if (! $destination)
                <form class="homepage-campaign-form" method="POST" action="{{ route('admin.group-tour-destinations.store') }}">
                    @csrf
                    <section class="homepage-form-panel">
                        <div class="homepage-form-panel-heading">
                            <span class="homepage-form-icon" aria-hidden="true">01</span>
                            <div><h2>Name the page</h2><p>The page is published at /group-tours/web-address and added to the Group Tours menu.</p></div>
                        </div>
                        <div class="homepage-form-grid">
                            <label class="homepage-form-field"><span>Destination name</span><input name="name" value="{{ old('name') }}" required maxlength="60" placeholder="e.g. Vietnam"></label>
                            <label class="homepage-form-field"><span>Web address <small class="homepage-field-hint">Optional · made from the name if empty</small></span><input name="slug" value="{{ old('slug') }}" maxlength="60" placeholder="e.g. vietnam"></label>
                        </div>
                    </section>
                    <div class="island-admin-form-actions">
                        <a class="homepage-editor-back" href="{{ route('admin.group-tours.edit') }}">Cancel</a>
                        <button class="admin-primary-button" type="submit">Create page <span aria-hidden="true">→</span></button>
                    </div>
                </form>
            @else
                <form class="homepage-campaign-form" data-form-slideshow method="POST" action="{{ route('admin.group-tour-destinations.update', $destination) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <section class="homepage-form-panel">
                        <div class="homepage-form-panel-heading">
                            <span class="homepage-form-icon" aria-hidden="true">01</span>
                            <div><h2>{{ \Illuminate\Support\Str::ucfirst($destination->placeTerm(2)) }}</h2><p>Each {{ $destination->placeTerm() }} is a card grouped under its region. Add up to {{ \App\Models\GroupTourDestination::MAX_STATES }}; empty cards are ignored. Without a photo the card shows a coloured tile.</p></div>
                        </div>

                        @php
                            $maxStates = \App\Models\GroupTourDestination::MAX_STATES;
                            $stateValues = array_values(array_filter(old('states', $destination->states ?? []), 'is_array'));
                            $stateValues = $stateValues ?: [array_fill_keys(['name', 'region', 'capital', 'tagline', 'image', 'alt'], '')];
                        @endphp
                        <div class="island-gallery-editor" data-gallery-editor data-max-slides="{{ $maxStates }}" data-field-prefix="states" data-item-label="{{ \Illuminate\Support\Str::ucfirst($destination->placeTerm()) }}" data-item-label-plural="{{ $destination->placeTerm(2) }}" data-gallery-paged="name" data-gallery-columns>
                            <div class="island-destination-fields-grid" data-gallery-slides>
                                @foreach ($stateValues as $index => $state)
                                    <fieldset class="island-entry-panel" data-gallery-slide>
                                        <legend data-slide-legend>{{ \Illuminate\Support\Str::ucfirst($destination->placeTerm()) }} {{ $index + 1 }}</legend>
                                        @include('Admin.group-tours.partials.state-fields', ['state' => $state, 'index' => $index])
                                        <button class="island-remove-slide" type="button" data-remove-slide aria-label="Remove {{ $destination->placeTerm() }} {{ $index + 1 }}">Remove {{ $destination->placeTerm() }}</button>
                                    </fieldset>
                                @endforeach
                            </div>
                            <div class="island-gallery-editor-actions">
                                <span data-gallery-count aria-live="polite">{{ count($stateValues) }} of {{ $maxStates }} {{ $destination->placeTerm(2) }}</span>
                                <button class="island-add-slide" type="button" data-add-slide>Add {{ $destination->placeTerm() }} <span aria-hidden="true">＋</span></button>
                            </div>
                        </div>

                        <template data-gallery-template>
                            <fieldset class="island-entry-panel" data-gallery-slide>
                                <legend data-slide-legend></legend>
                                @include('Admin.group-tours.partials.state-fields', ['state' => [], 'index' => null])
                                <button class="island-remove-slide" type="button" data-remove-slide>Remove {{ $destination->placeTerm() }}</button>
                            </fieldset>
                        </template>
                    </section>

                    <section class="homepage-form-panel">
                        <div class="homepage-form-panel-heading">
                            <span class="homepage-form-icon" aria-hidden="true">02</span>
                            <div><h2>Regions</h2><p>The regions this page groups its {{ $destination->placeTerm(2) }} into, each with a photo slide. Rename a region freely; clear its name to remove it once no {{ $destination->placeTerm(2) }} use it. New regions appear in the region list after saving.</p></div>
                        </div>
                        <div class="homepage-form-grid">
                            <label class="homepage-form-field"><span>Word for the places <small class="homepage-field-hint">Singular, e.g. state, province or destination</small></span><input name="place_term" value="{{ old('place_term', $destination->place_term) }}" required maxlength="30" pattern="[A-Za-z ]+"></label>
                        </div>
                        <div class="island-destination-fields-grid">
                            @foreach ($destination->regionOptions() as $regionKey => $regionLabel)
                                @php($regionImage = old("region_images.$regionKey", $destination->region_images[$regionKey] ?? []))
                                <fieldset class="island-entry-panel">
                                    <legend>{{ $regionLabel }}</legend>
                                    <label class="homepage-form-field"><span>Region name <small class="homepage-field-hint">Clear to remove</small></span><input name="regions[{{ $regionKey }}]" value="{{ old("regions.$regionKey", $regionLabel) }}" maxlength="40"></label>
                                    @include('Admin.partials.image-clear-field', [
                                        'value' => (string) ($regionImage['image'] ?? ''),
                                        'previewSrc' => filled($regionImage['image'] ?? '') ? $destination->imageUrl($regionImage['image']) : '',
                                        'urlAttributes' => 'name="region_images['.$regionKey.'][image]"',
                                        'fileAttributes' => 'name="region_images['.$regionKey.'][image_file]"',
                                        'defaultTitle' => 'Default photo',
                                        'defaultHint' => 'Uses the first photo of a place in this region, or the banner',
                                    ])
                                    <label class="homepage-form-field"><span>Photo alt text <small class="homepage-field-hint">Optional</small></span><input name="region_images[{{ $regionKey }}][alt]" value="{{ $regionImage['alt'] ?? '' }}" maxlength="255"></label>
                                </fieldset>
                            @endforeach
                            @if (count($destination->regionOptions()) < \App\Models\GroupTourDestination::MAX_REGIONS)
                                <fieldset class="island-entry-panel">
                                    <legend>Add regions</legend>
                                    @foreach (range(0, min(1, \App\Models\GroupTourDestination::MAX_REGIONS - count($destination->regionOptions()) - 1)) as $newRegionIndex)
                                        <label class="homepage-form-field"><span>New region name <small class="homepage-field-hint">Optional</small></span><input name="new_regions[]" value="{{ old("new_regions.$newRegionIndex") }}" maxlength="40" placeholder="e.g. Highlands"></label>
                                    @endforeach
                                </fieldset>
                            @endif
                        </div>
                    </section>

                    <section class="homepage-form-panel">
                        <div class="homepage-form-panel-heading">
                            <span class="homepage-form-icon" aria-hidden="true">03</span>
                            <div><h2>Banner &amp; page details</h2><p>The banner at the top of the page, the heading above the {{ $destination->placeTerm(2) }}, and the search description.</p></div>
                        </div>
                        <div class="homepage-form-grid">
                            <label class="homepage-form-field"><span>Destination name <small class="homepage-field-hint">Used in the menu</small></span><input name="name" value="{{ old('name', $destination->name) }}" required maxlength="60"></label>
                            <label class="homepage-form-field"><span>Tag</span><input name="hero_eyebrow" value="{{ old('hero_eyebrow', $destination->hero_eyebrow) }}" required maxlength="60"></label>
                            <label class="homepage-form-field"><span>Heading</span><input name="heading" value="{{ old('heading', $destination->heading) }}" required maxlength="120"></label>
                            <label class="homepage-form-field"><span>States heading</span><input name="states_heading" value="{{ old('states_heading', $destination->states_heading) }}" required maxlength="120"></label>
                            <label class="homepage-image-field">
                                <span>Banner image <small class="homepage-field-hint">Leave empty to keep current image</small></span>
                                <img src="{{ $destination->imageUrl($destination->hero_image) }}" alt="" loading="lazy">
                                <input type="file" name="hero_image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                            </label>
                            <label class="homepage-form-field homepage-form-field-wide"><span>Introduction <small class="homepage-field-hint">Optional · shown under the heading</small></span><textarea name="intro" maxlength="500">{{ old('intro', $destination->intro) }}</textarea></label>
                            <label class="homepage-form-field homepage-form-field-wide"><span>Search description</span><textarea name="meta_description" required maxlength="500">{{ old('meta_description', $destination->meta_description) }}</textarea></label>
                        </div>
                    </section>

                    <div class="island-admin-form-actions">
                        <button class="admin-primary-button" type="submit">Save {{ $destination->name }} page <span aria-hidden="true">→</span></button>
                    </div>
                </form>
            @endif
        </main>
    </div>
</body>
</html>
