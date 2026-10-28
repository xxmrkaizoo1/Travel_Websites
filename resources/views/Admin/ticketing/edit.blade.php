<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f4f7fc">
    <meta name="description" content="Manage the Smart Holidays ticketing page.">
    <title>Ticketing | Smart Holidays Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('Admin.partials.theme-head')
    <style>@view-transition { navigation: auto; }</style>
</head>
<body class="admin-page">
    <a class="skip-link" href="#ticketing-admin-main">Skip to ticketing content</a>

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
                <a href="{{ route('ticketing') }}" target="_blank" rel="noreferrer">View page ↗</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit">Sign out</button>
                </form>
            </nav>
        </header>

        <main class="homepage-editor-main" id="ticketing-admin-main">
            <div class="admin-breadcrumb homepage-editor-breadcrumb">
                <a href="{{ route('admin.dashboard') }}">Workspace</a>
                <span aria-hidden="true">/</span>
                <span>Smart Holidays</span>
                <span aria-hidden="true">/</span>
                <strong>Ticketing</strong>
            </div>

            <section class="homepage-editor-heading">
                <div>
                    <span class="admin-eyebrow">SMART HOLIDAYS</span>
                    <h1>Ticketing page</h1>
                    <p>Update the banner, airline partners, fare promotions and the ticket enquiry box.</p>
                </div>
                <span class="homepage-editor-count">{{ count($page->promotions) }} promotions</span>
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

            <form class="homepage-campaign-form" data-form-slideshow method="POST" action="{{ route('admin.ticketing.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <section class="homepage-form-panel">
                    <div class="homepage-form-panel-heading">
                        <span class="homepage-form-icon" aria-hidden="true">01</span>
                        <div><h2>Banner &amp; page details</h2><p>The sky banner at the top of the page, plus the browser title and search description. Clear the image to go back to the plane banner.</p></div>
                    </div>
                    <div class="homepage-form-grid">
                        <label class="homepage-form-field"><span>Tag</span><input name="hero_eyebrow" value="{{ old('hero_eyebrow', $page->hero_eyebrow) }}" required maxlength="60"></label>
                        <label class="homepage-form-field"><span>Heading</span><input name="hero_title" value="{{ old('hero_title', $page->hero_title) }}" required maxlength="80"></label>
                        <label class="homepage-form-field"><span>Heading, second line</span><input name="hero_highlight" value="{{ old('hero_highlight', $page->hero_highlight) }}" required maxlength="80"></label>
                        <label class="homepage-form-field homepage-form-field-wide"><span>Text</span><textarea name="hero_body" required maxlength="500">{{ old('hero_body', $page->hero_body) }}</textarea></label>
                        @php $heroImage = old('hero_image', $page->hero_image); @endphp
                        @include('Admin.partials.image-clear-field', [
                            'value' => (string) $heroImage,
                            'previewSrc' => filled($heroImage) ? $page->imageUrl($heroImage) : '',
                            'urlAttributes' => 'name="hero_image"',
                            'fileAttributes' => 'name="hero_image_file"',
                            'defaultTitle' => 'Default banner',
                            'defaultHint' => 'The plane-in-the-sky banner shows until you add your own',
                        ])
                        <label class="homepage-form-field"><span>Browser title</span><input name="meta_title" value="{{ old('meta_title', $page->meta_title) }}" required maxlength="120"></label>
                        <label class="homepage-form-field homepage-form-field-wide"><span>Search description</span><textarea name="meta_description" required maxlength="500">{{ old('meta_description', $page->meta_description) }}</textarea></label>
                    </div>
                </section>

                <section class="homepage-form-panel">
                    <div class="homepage-form-panel-heading">
                        <span class="homepage-form-icon" aria-hidden="true">02</span>
                        <div><h2>Airline partners</h2><p>The scrolling strip of airline logos. Each airline also appears in the enquiry form's airline list. Up to {{ \App\Models\TicketingPage::MAX_AIRLINES }}; empty rows are ignored.</p></div>
                    </div>
                    <div class="homepage-form-grid">
                        <label class="homepage-form-field"><span>Label</span><input name="airlines_label" value="{{ old('airlines_label', $page->airlines_label) }}" required maxlength="60"></label>
                        <label class="homepage-form-field"><span>Heading</span><input name="airlines_heading" value="{{ old('airlines_heading', $page->airlines_heading) }}" required maxlength="120"></label>
                    </div>

                    @php
                        $maxAirlines = \App\Models\TicketingPage::MAX_AIRLINES;
                        $airlineValues = array_values(array_filter(old('airlines', $page->airlines), 'is_array'));
                    @endphp
                    <div class="island-gallery-editor" data-gallery-editor data-max-slides="{{ $maxAirlines }}" data-field-prefix="airlines" data-item-label="Airline" data-item-label-plural="airlines" data-gallery-paged="name" data-gallery-columns>
                        <div class="island-destination-fields-grid" data-gallery-slides>
                            @foreach ($airlineValues as $index => $airline)
                                <fieldset class="island-entry-panel" data-gallery-slide>
                                    <legend data-slide-legend>Airline {{ $index + 1 }}</legend>
                                    @if (! empty($airline['logo']))
                                        <img class="tour-package-slide-preview" src="{{ $page->imageUrl($airline['logo']) }}" alt="" loading="lazy" width="160">
                                    @endif
                                    <label class="homepage-form-field"><span>Airline name</span><input data-gallery-field="name" data-gallery-optional name="airlines[{{ $index }}][name]" value="{{ $airline['name'] ?? '' }}" maxlength="60"></label>
                                    <label class="homepage-form-field"><span>Logo URL <small class="homepage-field-hint">Or upload a file</small></span><input data-gallery-field="logo" data-gallery-optional name="airlines[{{ $index }}][logo]" value="{{ $airline['logo'] ?? '' }}" maxlength="2048"></label>
                                    <label class="homepage-form-field homepage-form-field-wide"><span>Upload logo <small class="homepage-field-hint">PNG on a white or clear background works best · replaces the URL</small></span><input type="file" data-gallery-field="logo_file" data-gallery-optional name="airlines[{{ $index }}][logo_file]" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"></label>
                                    <button class="island-remove-slide" type="button" data-remove-slide aria-label="Remove airline {{ $index + 1 }}">Remove airline</button>
                                </fieldset>
                            @endforeach
                        </div>
                        <div class="island-gallery-editor-actions">
                            <span data-gallery-count aria-live="polite">{{ count($airlineValues) }} of {{ $maxAirlines }} airlines</span>
                            <button class="island-add-slide" type="button" data-add-slide>Add airline <span aria-hidden="true">＋</span></button>
                        </div>
                    </div>

                    <template data-gallery-template>
                        <fieldset class="island-entry-panel" data-gallery-slide>
                            <legend data-slide-legend></legend>
                            <label class="homepage-form-field"><span>Airline name</span><input data-gallery-field="name" data-gallery-optional maxlength="60"></label>
                            <label class="homepage-form-field"><span>Logo URL <small class="homepage-field-hint">Or upload a file</small></span><input data-gallery-field="logo" data-gallery-optional maxlength="2048"></label>
                            <label class="homepage-form-field homepage-form-field-wide"><span>Upload logo <small class="homepage-field-hint">PNG on a white or clear background works best · replaces the URL</small></span><input type="file" data-gallery-field="logo_file" data-gallery-optional accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"></label>
                            <button class="island-remove-slide" type="button" data-remove-slide>Remove airline</button>
                        </fieldset>
                    </template>
                </section>

                <section class="homepage-form-panel">
                    <div class="homepage-form-panel-heading">
                        <span class="homepage-form-icon" aria-hidden="true">03</span>
                        <div><h2>Fare promotions</h2><p>Poster cards with the fare, routes and dates. Each card's button opens WhatsApp about that fare. Remove a promotion when it ends. Up to {{ \App\Models\TicketingPage::MAX_PROMOTIONS }}; remove them all to hide the section.</p></div>
                    </div>
                    <div class="homepage-form-grid">
                        <label class="homepage-form-field"><span>Label</span><input name="promotions_label" value="{{ old('promotions_label', $page->promotions_label) }}" required maxlength="60"></label>
                        <label class="homepage-form-field"><span>Heading</span><input name="promotions_heading" value="{{ old('promotions_heading', $page->promotions_heading) }}" required maxlength="120"></label>
                        <label class="homepage-form-field homepage-form-field-wide"><span>Text <small class="homepage-field-hint">Optional</small></span><textarea name="promotions_body" maxlength="500">{{ old('promotions_body', $page->promotions_body) }}</textarea></label>
                    </div>

                    @php
                        $maxPromotions = \App\Models\TicketingPage::MAX_PROMOTIONS;
                        $promotionValues = array_values(array_filter(old('promotions', $page->promotions), 'is_array'));
                    @endphp
                    <div class="island-gallery-editor" data-gallery-editor data-max-slides="{{ $maxPromotions }}" data-field-prefix="promotions" data-item-label="Promotion" data-item-label-plural="promotions" data-gallery-paged="title" data-gallery-columns>
                        <div class="island-destination-fields-grid" data-gallery-slides>
                            @foreach ($promotionValues as $index => $promotion)
                                <fieldset class="island-entry-panel" data-gallery-slide>
                                    <legend data-slide-legend>Promotion {{ $index + 1 }}</legend>
                                    @if (! empty($promotion['image']))
                                        <img class="tour-package-slide-preview" src="{{ $page->imageUrl($promotion['image']) }}" alt="" loading="lazy" width="160">
                                    @endif
                                    <label class="homepage-form-field"><span>Title</span><input data-gallery-field="title" data-gallery-optional name="promotions[{{ $index }}][title]" value="{{ $promotion['title'] ?? '' }}" maxlength="60"></label>
                                    <label class="homepage-form-field"><span>Airline</span><input data-gallery-field="airline" data-gallery-optional name="promotions[{{ $index }}][airline]" value="{{ $promotion['airline'] ?? '' }}" maxlength="60"></label>
                                    <label class="homepage-form-field"><span>Flying from <small class="homepage-field-hint">Optional</small></span><input data-gallery-field="origin" data-gallery-optional name="promotions[{{ $index }}][origin]" value="{{ $promotion['origin'] ?? '' }}" maxlength="60"></label>
                                    <label class="homepage-form-field"><span>Fare <small class="homepage-field-hint">Optional · e.g. RM189</small></span><input data-gallery-field="fare" data-gallery-optional name="promotions[{{ $index }}][fare]" value="{{ $promotion['fare'] ?? '' }}" maxlength="20"></label>
                                    <label class="homepage-form-field homepage-form-field-wide"><span>Destinations <small class="homepage-field-hint">Optional</small></span><input data-gallery-field="destinations" data-gallery-optional name="promotions[{{ $index }}][destinations]" value="{{ $promotion['destinations'] ?? '' }}" maxlength="200"></label>
                                    <label class="homepage-form-field"><span>Fare note <small class="homepage-field-hint">Optional · e.g. All-in one way from</small></span><input data-gallery-field="fare_note" data-gallery-optional name="promotions[{{ $index }}][fare_note]" value="{{ $promotion['fare_note'] ?? '' }}" maxlength="40"></label>
                                    <label class="homepage-form-field"><span>Booking period <small class="homepage-field-hint">Optional</small></span><input data-gallery-field="booking_period" data-gallery-optional name="promotions[{{ $index }}][booking_period]" value="{{ $promotion['booking_period'] ?? '' }}" maxlength="60"></label>
                                    <label class="homepage-form-field"><span>Travel period <small class="homepage-field-hint">Optional</small></span><input data-gallery-field="travel_period" data-gallery-optional name="promotions[{{ $index }}][travel_period]" value="{{ $promotion['travel_period'] ?? '' }}" maxlength="60"></label>
                                    <label class="homepage-form-field"><span>Poster URL <small class="homepage-field-hint">Or upload a file</small></span><input data-gallery-field="image" data-gallery-optional name="promotions[{{ $index }}][image]" value="{{ $promotion['image'] ?? '' }}" maxlength="2048"></label>
                                    <label class="homepage-form-field"><span>Upload poster <small class="homepage-field-hint">Portrait image · replaces the URL</small></span><input type="file" data-gallery-field="image_file" data-gallery-optional name="promotions[{{ $index }}][image_file]" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"></label>
                                    <label class="homepage-form-field homepage-form-field-wide"><span>Poster alt text</span><input data-gallery-field="alt" data-gallery-optional name="promotions[{{ $index }}][alt]" value="{{ $promotion['alt'] ?? '' }}" maxlength="255"></label>
                                    <button class="island-remove-slide" type="button" data-remove-slide aria-label="Remove promotion {{ $index + 1 }}">Remove promotion</button>
                                </fieldset>
                            @endforeach
                        </div>
                        <div class="island-gallery-editor-actions">
                            <span data-gallery-count aria-live="polite">{{ count($promotionValues) }} of {{ $maxPromotions }} promotions</span>
                            <button class="island-add-slide" type="button" data-add-slide>Add promotion <span aria-hidden="true">＋</span></button>
                        </div>
                    </div>

                    <template data-gallery-template>
                        <fieldset class="island-entry-panel" data-gallery-slide>
                            <legend data-slide-legend></legend>
                            <label class="homepage-form-field"><span>Title</span><input data-gallery-field="title" data-gallery-optional maxlength="60"></label>
                            <label class="homepage-form-field"><span>Airline</span><input data-gallery-field="airline" data-gallery-optional maxlength="60"></label>
                            <label class="homepage-form-field"><span>Flying from <small class="homepage-field-hint">Optional</small></span><input data-gallery-field="origin" data-gallery-optional maxlength="60" value="Kuala Lumpur"></label>
                            <label class="homepage-form-field"><span>Fare <small class="homepage-field-hint">Optional · e.g. RM189</small></span><input data-gallery-field="fare" data-gallery-optional maxlength="20"></label>
                            <label class="homepage-form-field homepage-form-field-wide"><span>Destinations <small class="homepage-field-hint">Optional</small></span><input data-gallery-field="destinations" data-gallery-optional maxlength="200"></label>
                            <label class="homepage-form-field"><span>Fare note <small class="homepage-field-hint">Optional · e.g. All-in one way from</small></span><input data-gallery-field="fare_note" data-gallery-optional maxlength="40"></label>
                            <label class="homepage-form-field"><span>Booking period <small class="homepage-field-hint">Optional</small></span><input data-gallery-field="booking_period" data-gallery-optional maxlength="60"></label>
                            <label class="homepage-form-field"><span>Travel period <small class="homepage-field-hint">Optional</small></span><input data-gallery-field="travel_period" data-gallery-optional maxlength="60"></label>
                            <label class="homepage-form-field"><span>Poster URL <small class="homepage-field-hint">Or upload a file</small></span><input data-gallery-field="image" data-gallery-optional maxlength="2048"></label>
                            <label class="homepage-form-field"><span>Upload poster <small class="homepage-field-hint">Portrait image · replaces the URL</small></span><input type="file" data-gallery-field="image_file" data-gallery-optional accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"></label>
                            <label class="homepage-form-field homepage-form-field-wide"><span>Poster alt text</span><input data-gallery-field="alt" data-gallery-optional maxlength="255"></label>
                            <button class="island-remove-slide" type="button" data-remove-slide>Remove promotion</button>
                        </fieldset>
                    </template>
                </section>

                <section class="homepage-form-panel">
                    <div class="homepage-form-panel-heading">
                        <span class="homepage-form-icon" aria-hidden="true">04</span>
                        <div><h2>Tickets enquiry &amp; contact</h2><p>The enquiry box at the end of the page. Its form sends the trip details to the WhatsApp number below.</p></div>
                    </div>
                    <div class="homepage-form-grid">
                        <label class="homepage-form-field"><span>Label</span><input name="enquiry_label" value="{{ old('enquiry_label', $page->enquiry_label) }}" required maxlength="60"></label>
                        <label class="homepage-form-field"><span>Heading</span><input name="enquiry_heading" value="{{ old('enquiry_heading', $page->enquiry_heading) }}" required maxlength="120"></label>
                        <label class="homepage-form-field homepage-form-field-wide"><span>Text</span><textarea name="enquiry_body" required maxlength="500">{{ old('enquiry_body', $page->enquiry_body) }}</textarea></label>
                        <label class="homepage-form-field"><span>WhatsApp number <small class="homepage-field-hint">Digits with country code, e.g. 60165500590</small></span><input name="whatsapp_number" value="{{ old('whatsapp_number', $page->whatsapp_number) }}" required maxlength="15" inputmode="numeric" pattern="[0-9]{8,15}"></label>
                        <label class="homepage-form-field"><span>Email</span><input type="email" name="email" value="{{ old('email', $page->email) }}" required maxlength="120"></label>
                        <label class="homepage-form-field"><span>Phone</span><input name="phone" value="{{ old('phone', $page->phone) }}" required maxlength="40"></label>
                    </div>
                </section>

                <div class="island-admin-form-actions">
                    <button class="admin-primary-button" type="submit">Save ticketing page <span aria-hidden="true">→</span></button>
                </div>
            </form>
        </main>
    </div>
</body>
</html>
