<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f4f7fc">
    <meta name="description" content="Manage the Company Trip (MICE) page content and clientele logos.">
    <title>Company Trip (MICE) | Smart Holidays Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>@view-transition { navigation: auto; }</style>
</head>
<body class="admin-page">
    <a class="skip-link" href="#mice-admin-main">Skip to Company Trip (MICE) content</a>

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
                <a href="{{ route('company-trip-mice') }}" target="_blank" rel="noreferrer">View page ↗</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit">Sign out</button>
                </form>
            </nav>
        </header>

        <main class="homepage-editor-main" id="mice-admin-main">
            <div class="admin-breadcrumb homepage-editor-breadcrumb">
                <a href="{{ route('admin.dashboard') }}">Workspace</a>
                <span aria-hidden="true">/</span>
                <strong>Company Trip (MICE)</strong>
            </div>

            <section class="homepage-editor-heading">
                <div>
                    <span class="admin-eyebrow">CORPORATE TRAVEL</span>
                    <h1>Company Trip (MICE)</h1>
                    <p>Update the page story, images, contact details and clientele logos.</p>
                </div>
                <span class="homepage-editor-count">{{ $clients->count() }} clientele logos</span>
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

            <form class="homepage-campaign-form" data-form-slideshow method="POST" action="{{ route('admin.mice.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <section class="homepage-form-panel">
                    <div class="homepage-form-panel-heading">
                        <span class="homepage-form-icon" aria-hidden="true">01</span>
                        <div><h2>Hero section</h2><p>The opening message and image at the top of the public page.</p></div>
                    </div>
                    <div class="homepage-form-grid">
                        <label class="homepage-form-field"><span>Eyebrow</span><input name="hero_eyebrow" value="{{ old('hero_eyebrow', $page->hero_eyebrow) }}" required maxlength="120"></label>
                        <label class="homepage-form-field"><span>Button label</span><input name="hero_cta_label" value="{{ old('hero_cta_label', $page->hero_cta_label) }}" required maxlength="100"></label>
                        <label class="homepage-form-field"><span>Headline</span><input name="hero_title" value="{{ old('hero_title', $page->hero_title) }}" required maxlength="120"></label>
                        <label class="homepage-form-field"><span>Highlighted headline</span><input name="hero_highlight" value="{{ old('hero_highlight', $page->hero_highlight) }}" required maxlength="120"></label>
                        <label class="homepage-form-field homepage-form-field-wide"><span>Introductory text</span><textarea name="hero_body" required maxlength="1500">{{ old('hero_body', $page->hero_body) }}</textarea></label>
                        <label class="homepage-image-field">
                            <span>Hero image <small class="homepage-field-hint">Leave empty to keep current image</small></span>
                            <img src="{{ $page->imageUrl($page->hero_image) }}" alt="" loading="lazy">
                            <input type="file" name="hero_image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                        </label>
                    </div>
                </section>

                <section class="homepage-form-panel">
                    <div class="homepage-form-panel-heading">
                        <span class="homepage-form-icon" aria-hidden="true">02</span>
                        <div><h2>Introduction</h2><p>Describe your MICE service. Separate paragraphs with a blank line.</p></div>
                    </div>
                    <div class="homepage-form-grid">
                        <label class="homepage-form-field"><span>Eyebrow</span><input name="intro_eyebrow" value="{{ old('intro_eyebrow', $page->intro_eyebrow) }}" required maxlength="120"></label>
                        <label class="homepage-form-field"><span>Headline</span><input name="intro_heading" value="{{ old('intro_heading', $page->intro_heading) }}" required maxlength="160"></label>
                        <label class="homepage-form-field"><span>Highlighted headline</span><input name="intro_highlight" value="{{ old('intro_highlight', $page->intro_highlight) }}" required maxlength="100"></label>
                        <label class="homepage-form-field"><span>Image caption</span><input name="intro_image_caption" value="{{ old('intro_image_caption', $page->intro_image_caption) }}" required maxlength="120"></label>
                        <label class="homepage-form-field homepage-form-field-wide"><span>Description</span><textarea name="intro_body" rows="8" required maxlength="3000">{{ old('intro_body', $page->intro_body) }}</textarea></label>
                        <label class="homepage-form-field"><span>Image alt text</span><input name="intro_image_alt" value="{{ old('intro_image_alt', $page->intro_image_alt) }}" required maxlength="255"></label>
                        <label class="homepage-image-field">
                            <span>Intro image <small class="homepage-field-hint">Leave empty to keep current image</small></span>
                            <img src="{{ $page->imageUrl($page->intro_image) }}" alt="" loading="lazy">
                            <input type="file" name="intro_image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                        </label>
                    </div>
                </section>

                <section class="homepage-form-panel">
                    <div class="homepage-form-panel-heading">
                        <span class="homepage-form-icon" aria-hidden="true">03</span>
                        <div><h2>Why Smart Muslim</h2><p>Four highlight cards shown below the introduction.</p></div>
                    </div>
                    <div class="homepage-form-grid">
                        <label class="homepage-form-field"><span>Eyebrow</span><input name="why_eyebrow" value="{{ old('why_eyebrow', $page->why_eyebrow) }}" required maxlength="120"></label>
                        <label class="homepage-form-field"><span>Headline</span><input name="why_heading" value="{{ old('why_heading', $page->why_heading) }}" required maxlength="160"></label>
                        @foreach ($page->highlights as $index => $highlight)
                            <label class="homepage-form-field"><span>Card {{ $index + 1 }} title</span><input name="highlights[{{ $index }}][title]" value="{{ old("highlights.$index.title", $highlight['title']) }}" required maxlength="100"></label>
                            <label class="homepage-form-field"><span>Card {{ $index + 1 }} text</span><textarea name="highlights[{{ $index }}][body]" required maxlength="500">{{ old("highlights.$index.body", $highlight['body']) }}</textarea></label>
                        @endforeach
                    </div>
                </section>

                <section class="homepage-form-panel">
                    <div class="homepage-form-panel-heading">
                        <span class="homepage-form-icon" aria-hidden="true">04</span>
                        <div><h2>Contact</h2><p>Details shown in the contact section of the public page.</p></div>
                    </div>
                    <div class="homepage-form-grid">
                        <label class="homepage-form-field"><span>Eyebrow</span><input name="contact_eyebrow" value="{{ old('contact_eyebrow', $page->contact_eyebrow) }}" required maxlength="120"></label>
                        <label class="homepage-form-field"><span>Headline</span><input name="contact_heading" value="{{ old('contact_heading', $page->contact_heading) }}" required maxlength="160"></label>
                        <label class="homepage-form-field homepage-form-field-wide"><span>Description</span><textarea name="contact_body" required maxlength="1500">{{ old('contact_body', $page->contact_body) }}</textarea></label>
                        <label class="homepage-form-field"><span>Phone 1</span><input name="phone_primary" value="{{ old('phone_primary', $page->phone_primary) }}" required maxlength="30"></label>
                        <label class="homepage-form-field"><span>Phone 2</span><input name="phone_secondary" value="{{ old('phone_secondary', $page->phone_secondary) }}" required maxlength="30"></label>
                        <label class="homepage-form-field"><span>Email</span><input type="email" name="email" value="{{ old('email', $page->email) }}" required maxlength="255"></label>
                        <label class="homepage-form-field"><span>WhatsApp number <small class="homepage-field-hint">Digits only, with country code (e.g. 60173886708)</small></span><input name="whatsapp_number" value="{{ old('whatsapp_number', $page->whatsapp_number) }}" required maxlength="20" inputmode="numeric"></label>
                    </div>
                </section>

                <section class="homepage-form-panel">
                    <div class="homepage-form-panel-heading">
                        <span class="homepage-form-icon" aria-hidden="true">05</span>
                        <div><h2>Clientele heading</h2><p>Text above the sliding client logos.</p></div>
                    </div>
                    <div class="homepage-form-grid">
                        <label class="homepage-form-field"><span>Eyebrow</span><input name="clientele_eyebrow" value="{{ old('clientele_eyebrow', $page->clientele_eyebrow) }}" required maxlength="120"></label>
                        <label class="homepage-form-field"><span>Headline</span><input name="clientele_heading" value="{{ old('clientele_heading', $page->clientele_heading) }}" required maxlength="160"></label>
                        <label class="homepage-form-field homepage-form-field-wide"><span>Subtitle</span><textarea name="clientele_body" required maxlength="500">{{ old('clientele_body', $page->clientele_body) }}</textarea></label>
                    </div>
                </section>

                <div class="island-admin-form-actions">
                    <button class="admin-primary-button" type="submit">Save page content <span aria-hidden="true">→</span></button>
                </div>
            </form>

            <section class="island-destination-admin" aria-labelledby="mice-clients-title">
                <div class="homepage-services-admin-heading">
                    <div>
                        <span class="admin-eyebrow">CLIENTELE SLIDER</span>
                        <h2 id="mice-clients-title">Client logos</h2>
                        <p>Add or remove logos shown in the automatic slider. JPG, PNG or WebP, up to 2 MB.</p>
                    </div>
                </div>

                <form class="homepage-campaign-form" method="POST" action="{{ route('admin.mice.clients.store') }}" enctype="multipart/form-data">
                    @csrf
                    <section class="homepage-form-panel">
                        <div class="homepage-form-grid">
                            <label class="homepage-form-field"><span>Client name</span><input name="name" value="{{ old('name') }}" required maxlength="120"></label>
                            <label class="homepage-form-field"><span>Logo</span><input type="file" name="logo" required accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"></label>
                        </div>
                        <div class="island-admin-form-actions">
                            <button class="admin-primary-button" type="submit">Add logo <span aria-hidden="true">＋</span></button>
                        </div>
                    </section>
                </form>

                <div class="island-destination-admin-grid">
                    @forelse ($clients as $client)
                        <article class="island-destination-admin-card">
                            <img src="{{ $client->logoUrl() }}" alt="{{ $client->name }}" loading="lazy" style="object-fit: contain; padding: 1rem; background: #fff;">
                            <div class="island-destination-admin-card-content">
                                <h3>{{ $client->name }}</h3>
                                <div class="island-destination-admin-actions">
                                    <form method="POST" action="{{ route('admin.mice.clients.destroy', $client) }}" onsubmit="return confirm('Remove this client logo?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="island-delete-button" type="submit">Delete</button>
                                    </form>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="homepage-editor-empty">No client logos have been added yet.</div>
                    @endforelse
                </div>
            </section>

            <section class="island-destination-admin" aria-labelledby="mice-gallery-admin-title">
                <div class="homepage-services-admin-heading">
                    <div>
                        <span class="admin-eyebrow">GALLERY SLIDES</span>
                        <h2 id="mice-gallery-admin-title">Gallery photos</h2>
                        <p>Photos shown in the automatic gallery slider after “Why Smart Muslim”. JPG, PNG or WebP, up to 5 MB.</p>
                    </div>
                </div>

                <form class="homepage-campaign-form" method="POST" action="{{ route('admin.mice.gallery.store') }}" enctype="multipart/form-data">
                    @csrf
                    <section class="homepage-form-panel">
                        <div class="homepage-form-grid">
                            <label class="homepage-form-field"><span>Caption (optional)</span><input name="caption" value="{{ old('caption') }}" maxlength="120"></label>
                            <label class="homepage-form-field"><span>Photo</span><input type="file" name="gallery_image" required accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"></label>
                        </div>
                        <div class="island-admin-form-actions">
                            <button class="admin-primary-button" type="submit">Add photo <span aria-hidden="true">＋</span></button>
                        </div>
                    </section>
                </form>

                <div class="island-destination-admin-grid">
                    @forelse ($galleryImages as $photo)
                        <article class="island-destination-admin-card">
                            <img src="{{ $photo->imageUrl() }}" alt="{{ $photo->caption }}" loading="lazy">
                            <div class="island-destination-admin-card-content">
                                <h3>{{ $photo->caption !== '' ? $photo->caption : 'No caption' }}</h3>
                                <div class="island-destination-admin-actions">
                                    <form method="POST" action="{{ route('admin.mice.gallery.destroy', $photo) }}" onsubmit="return confirm('Remove this gallery photo?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="island-delete-button" type="submit">Delete</button>
                                    </form>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="homepage-editor-empty">No gallery photos yet. The gallery section stays hidden until you add one.</div>
                    @endforelse
                </div>
            </section>
        </main>

        <footer class="homepage-editor-footer">Smart Holidays · Company Trip (MICE) content manager</footer>
    </div>
</body>
</html>
