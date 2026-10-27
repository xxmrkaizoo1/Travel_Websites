<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f4f7fc">
    <meta name="description" content="Create or edit a China Muslim series departure card.">
    <title>{{ $package ? 'Edit' : 'New' }} China Muslim card | Smart Holidays Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>@view-transition { navigation: auto; }</style>
</head>
<body class="admin-page">
    <a class="skip-link" href="#china-muslim-form">Skip to card form</a>

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
                <a href="{{ route('admin.china-muslim') }}">All cards</a>
                <a href="{{ route('china-muslim') }}" target="_blank" rel="noreferrer">View website ↗</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit">Sign out</button>
                </form>
            </nav>
        </header>

        <main class="homepage-editor-main" id="china-muslim-form">
            <div class="admin-breadcrumb homepage-editor-breadcrumb">
                <a href="{{ route('admin.china-muslim') }}">China Muslim</a>
                <span aria-hidden="true">/</span>
                <strong>{{ $package ? 'Edit card' : 'New card' }}</strong>
            </div>

            <section class="homepage-editor-heading">
                <div>
                    <span class="admin-eyebrow">CHINA MUSLIM SERIES DEPARTURE</span>
                    <h1>{{ $package ? 'Edit card' : 'Create a new card' }}</h1>
                    <p>Add the photo, title, price and trip length shown on the card.</p>
                </div>
                <a class="homepage-editor-back" href="{{ route('admin.china-muslim') }}">← All cards</a>
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

            @if ($package)
                @include('Admin.partials.booking-switch', ['package' => $package])
            @endif

            <form class="homepage-campaign-form" data-form-slideshow data-no-field-preview method="POST" enctype="multipart/form-data"
                action="{{ $package ? route('admin.china-muslim.update', $package) : route('admin.china-muslim.store') }}">
                @csrf
                @if ($package)
                    @method('PUT')
                @endif

                <section class="homepage-form-panel">
                    <div class="homepage-form-panel-heading">
                        <span class="homepage-form-icon" aria-hidden="true">01</span>
                        <div>
                            <h2>Card details</h2>
                            <p>The title, price and duration appear on the card.</p>
                        </div>
                    </div>

                    <div class="homepage-form-grid">
                        <label class="homepage-form-field homepage-form-field-wide">
                            <span>Title</span>
                            <input name="title" value="{{ old('title', $package?->title) }}" maxlength="160" required placeholder="8D7N SHANGHAI SUZHOU HANGZHOU NANJING MUSLIM TOURS">
                            @error('title') <small>{{ $message }}</small> @enderror
                        </label>
                        <div class="china-fields-three">
                        <label class="homepage-form-field">
                            <span>Price (RM)</span>
                            <input name="price" type="number" min="0" max="9999999" step="1" value="{{ old('price', $package?->price ?? 0) }}" required>
                            @error('price') <small>{{ $message }}</small> @enderror
                        </label>
                        <label class="homepage-form-field">
                            <span>Days</span>
                            <input name="days" type="number" min="1" max="60" value="{{ old('days', $package?->days ?? 8) }}" required>
                            @error('days') <small>{{ $message }}</small> @enderror
                        </label>
                        <label class="homepage-form-field">
                            <span>Nights</span>
                            <input name="nights" type="number" min="0" max="60" value="{{ old('nights', $package?->nights ?? 7) }}" required>
                            @error('nights') <small>{{ $message }}</small> @enderror
                        </label>
                        </div>
                        <label class="homepage-form-field homepage-form-field-wide">
                            <span>Link <small>Optional. Where the card opens.</small></span>
                            <input name="link" type="url" value="{{ old('link', $package?->link) }}" maxlength="2048" placeholder="https://">
                            @error('link') <small>{{ $message }}</small> @enderror
                        </label>
                        <label class="china-hot-toggle">
                            <input type="checkbox" name="is_hot" value="1" @checked(old('is_hot', $package?->is_hot))>
                            <span class="china-hot-switch" aria-hidden="true"></span>
                            <span class="china-hot-text">
                                <strong>🔥 Show the “HOT” badge</strong>
                                <small>Highlights this card with an orange badge on the page.</small>
                            </span>
                        </label>
                    </div>
                </section>

                @php
                    $parsedDetails = $package?->parsedDetails() ?? ['days' => [], 'note' => '', 'sections' => []];
                    $slideDays = old('slides', collect($parsedDetails['days'])->map(fn ($day) => [
                        'title' => $day['title'],
                        'meals' => $day['meals'],
                        'items' => implode("\n", $day['items']),
                    ])->all());
                    $slideDays = array_values($slideDays);
                    $sectionHeadings = \App\Models\ChinaMuslimPackage::sectionHeadings();
                    $sectionIcons = ['departures' => '📅', 'accommodation' => '🏨', 'mandatory' => '🏛️', 'shopping' => '🛍️', 'optional' => '🎟️', 'included' => '✅', 'excluded' => '❌', 'duration' => '⏱️', 'language' => '🗣️'];
                @endphp

                <section class="homepage-form-panel" data-china-slides>
                    <div class="homepage-form-panel-heading">
                        <span class="homepage-form-icon" aria-hidden="true">02</span>
                        <div>
                            <h2>Popup slides</h2>
                            <p>Each card below becomes one slide in the popup. Add as many days as you need.</p>
                        </div>
                    </div>

                    <div class="china-edit-slider" data-china-slider>
                        <button class="china-edit-arrow" type="button" data-china-slider-prev aria-label="Previous slides">←</button>
                        <div class="china-edit-slides china-edit-track" data-china-slide-list>
                            @foreach ($slideDays as $slideIndex => $slideDay)
                                @include('Admin.china-muslim.partials.day-slide', ['index' => $slideIndex, 'day' => $slideDay])
                            @endforeach
                        </div>
                        <button class="china-edit-arrow" type="button" data-china-slider-next aria-label="Next slides">→</button>
                    </div>

                    <button class="china-edit-add" type="button" data-china-add-slide>＋ Add new slide</button>

                    <template data-china-slide-template>
                        @include('Admin.china-muslim.partials.day-slide', ['index' => '__INDEX__', 'day' => ['title' => '', 'meals' => '', 'items' => '']])
                    </template>

                    <h3 class="china-edit-subtitle">Info slides</h3>
                    <div class="china-edit-slider" data-china-slider>
                        <button class="china-edit-arrow" type="button" data-china-slider-prev aria-label="Previous slides">←</button>
                        <div class="china-edit-slides china-edit-track">
                            @foreach ($sectionHeadings as $sectionKey => $sectionHeading)
                            <div class="china-edit-slide china-edit-slide-info">
                                <div class="china-edit-slide-head">
                                    <span class="china-edit-slide-badge">{{ $sectionIcons[$sectionKey] }} {{ strtoupper($sectionHeading) }}</span>
                                </div>
                                <label class="homepage-form-field">
                                    <span>One item per line</span>
                                    <textarea name="sections[{{ $sectionKey }}]" rows="4" maxlength="4000">{{ old('sections.'.$sectionKey, implode("\n", $parsedDetails['sections'][$sectionHeading] ?? [])) }}</textarea>
                                    @error('sections.'.$sectionKey) <small>{{ $message }}</small> @enderror
                                </label>
                            </div>
                        @endforeach
                        <div class="china-edit-slide china-edit-slide-info">
                            <div class="china-edit-slide-head">
                                <span class="china-edit-slide-badge">📝 NOTE</span>
                            </div>
                            <label class="homepage-form-field">
                                <span>Shown on the “Good to know” slide</span>
                                <textarea name="note" rows="4" maxlength="1000">{{ old('note', $parsedDetails['note']) }}</textarea>
                                @error('note') <small>{{ $message }}</small> @enderror
                            </label>
                        </div>
                        </div>
                        <button class="china-edit-arrow" type="button" data-china-slider-next aria-label="Next slides">→</button>
                    </div>
                </section>

                <section class="homepage-form-panel">
                    <div class="homepage-form-panel-heading">
                        <span class="homepage-form-icon" aria-hidden="true">03</span>
                        <div>
                            <h2>Card photo</h2>
                            <p>Choose a JPG, PNG or WebP image up to 5 MB.</p>
                        </div>
                    </div>

                    <div class="homepage-image-grid homepage-image-grid-single">
                        <label class="homepage-image-field">
                            @if ($package)
                                <img src="{{ $package->imageUrl() }}" alt="Current card photo">
                                <span class="homepage-image-name">Current photo</span>
                            @endif
                            <input type="file" name="image" accept="image/jpeg,image/png,image/webp" @required(! $package)>
                            @error('image') <small>{{ $message }}</small> @enderror
                        </label>
                    </div>
                </section>

                <section class="homepage-form-panel china-video-editor">
                    <div class="homepage-form-panel-heading">
                        <span class="homepage-form-icon" aria-hidden="true">04</span>
                        <div>
                            <h2>About this place video</h2>
                            <p>Plays at the top of this card’s “About this place” popup. Upload a video file, or paste a YouTube link. An uploaded file is shown first.</p>
                        </div>
                    </div>

                    @if ($currentVideo = $package?->aboutVideo())
                        <div class="china-video-current">
                            @if ($currentVideo['type'] === 'file')
                                <video src="{{ $currentVideo['src'] }}" controls preload="metadata"></video>
                            @else
                                <iframe src="{{ $currentVideo['src'] }}" title="Current YouTube video" loading="lazy" allow="encrypted-media; picture-in-picture; fullscreen" allowfullscreen></iframe>
                            @endif
                            @if ($package->about_video_path)
                                <label class="china-travellers-remove"><input type="checkbox" name="remove_about_video" value="1" @checked(old('remove_about_video'))> Remove uploaded video</label>
                            @endif
                        </div>
                    @endif

                    <div class="homepage-form-grid">
                        <label class="homepage-form-field">
                            <span>Upload video <small class="homepage-field-hint">MP4, WebM or MOV, up to 100 MB.</small></span>
                            <input type="file" name="about_video" accept="video/mp4,video/webm,video/quicktime,.mp4,.webm,.mov">
                            @error('about_video') <small class="china-video-error">{{ $message }}</small> @enderror
                        </label>
                        <label class="homepage-form-field">
                            <span>Or YouTube link <small class="homepage-field-hint">Leave empty for no YouTube video.</small></span>
                            <input type="url" name="about_video_url" value="{{ old('about_video_url', $package?->about_video_url) }}" maxlength="2048" placeholder="https://www.youtube.com/watch?v=…">
                            @error('about_video_url') <small class="china-video-error">{{ $message }}</small> @enderror
                        </label>
                    </div>
                </section>

                @isset($about)
                    @include('Admin.china-muslim.partials.about-panel')
                @endisset

                <div class="homepage-form-actions">
                    <a class="homepage-editor-back" href="{{ route('admin.china-muslim') }}">Cancel</a>
                    <button class="admin-primary-button" type="submit">{{ $package ? 'Save card' : 'Create card' }} <span aria-hidden="true">→</span></button>
                </div>
            </form>

            @isset($about)
                <form id="china-about-form" method="POST" action="{{ route('admin.china-muslim.about') }}" enctype="multipart/form-data" hidden>
                    @csrf
                    @method('PUT')
                </form>
            @endisset
        </main>

        <footer class="homepage-editor-footer">Smart Holidays · Website content manager</footer>
    </div>
</body>
</html>
