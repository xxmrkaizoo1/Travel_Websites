<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f4f7fc">
    <meta name="description" content="Manage China Muslim series departure cards.">
    <title>China Muslim | Smart Holidays Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('Admin.partials.theme-head')
    <style>@view-transition { navigation: auto; }</style>
</head>
<body class="admin-page">
    <a class="skip-link" href="#china-muslim-admin">Skip to China Muslim cards</a>

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
                <a href="{{ route('china-muslim') }}" target="_blank" rel="noreferrer">View website ↗</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit">Sign out</button>
                </form>
            </nav>
        </header>

        <main class="homepage-editor-main" id="china-muslim-admin">
            <div class="admin-breadcrumb homepage-editor-breadcrumb">
                <a href="{{ route('admin.dashboard') }}">Smart Muslim Tour</a>
                <span aria-hidden="true">/</span>
                <strong>China Muslim</strong>
            </div>

            <section class="homepage-editor-heading">
                <div>
                    <span class="admin-eyebrow">CHINA MUSLIM</span>
                    <h1>Series departure cards</h1>
                    <p>These cards appear on the China Muslim page. Create a new card or edit an existing one.</p>
                </div>
                <a class="admin-primary-button" href="{{ route('admin.china-muslim.create') }}">New card <span aria-hidden="true">＋</span></a>
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

            @if ($packages->isEmpty())
                <p class="china-muslim-empty">No cards yet. Select “New card” to create the first one.</p>
            @else
                <div class="china-admin-toolbar" data-china-search>
                    <label class="china-admin-search">
                        <span aria-hidden="true">🔍</span>
                        <input type="search" placeholder="Search by title, price or days…" data-china-search-input autocomplete="off" aria-label="Search cards">
                        <button type="button" data-china-search-clear aria-label="Clear search" hidden>×</button>
                    </label>
                    <div class="china-admin-filters" role="group" aria-label="Filter cards">
                        <button type="button" class="is-active" data-china-filter="all">All <b>{{ $packages->count() }}</b></button>
                        <button type="button" data-china-filter="hot">🔥 Hot <b>{{ $packages->where('is_hot', true)->count() }}</b></button>
                        <button type="button" data-china-filter="details">With popup <b>{{ $packages->filter(fn ($p) => filled($p->details))->count() }}</b></button>
                    </div>
                    <p class="china-admin-count"><span data-china-visible>{{ $packages->count() }}</span> of {{ $packages->count() }} cards</p>
                </div>

                <div class="china-admin-list" data-china-list>
                    @foreach ($packages as $package)
                        <article class="china-admin-item" data-china-item data-hot="{{ $package->is_hot ? 1 : 0 }}" data-details="{{ filled($package->details) ? 1 : 0 }}"
                            data-search="{{ mb_strtolower($package->title.' '.$package->formattedPrice().' '.$package->price.' '.$package->days.'d '.$package->nights.'n '.$package->days.' days '.($package->is_hot ? 'hot' : '')) }}">
                            <img src="{{ $package->imageUrl() }}" alt="">
                            <div class="china-admin-item-body">
                                <strong>{{ $package->title }}</strong>
                                <div class="china-admin-meta">
                                    <span class="china-admin-chip china-admin-chip-price">{{ $package->formattedPrice() }}</span>
                                    <span class="china-admin-chip">🗓️ {{ $package->days }}D {{ $package->nights }}N</span>
                                    @if ($package->is_hot)
                                        <span class="china-admin-chip china-admin-chip-hot">🔥 HOT</span>
                                    @endif
                                    @if (filled($package->details))
                                        <span class="china-admin-chip">▤ {{ count($package->parsedDetails()['days']) }} day slides</span>
                                    @endif
                                </div>
                            </div>
                            <div class="china-admin-item-actions">
                                @include('Admin.partials.booking-switch', ['package' => $package, 'compact' => true])
                                <a class="admin-primary-button" href="{{ route('admin.china-muslim.edit', $package) }}">Edit</a>
                                <form method="POST" action="{{ route('admin.china-muslim.destroy', $package) }}" onsubmit="return confirm('Delete this China Muslim card?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="china-admin-delete" type="submit">Delete</button>
                                </form>
                            </div>
                        </article>
                    @endforeach
                </div>
                <p class="china-muslim-empty" data-china-no-results hidden>No cards match your search.</p>
            @endif

            @if ($about)
                <form class="homepage-form-panel china-travellers-editor" method="POST" action="{{ route('admin.china-muslim.travellers') }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="homepage-form-panel-heading">
                        <span class="homepage-form-icon" aria-hidden="true">★</span>
                        <div>
                            <h2>Traveller photos slideshow</h2>
                            <p>Group photos from past trips, shown full width below the packages on the China Muslim page. Edit captions, tick photos to remove, or upload new ones.</p>
                        </div>
                    </div>

                    @if (count($about->traveller_photos ?? []) > 0)
                        <div class="china-travellers-grid">
                            @foreach ($about->traveller_photos as $index => $photo)
                                <div class="china-travellers-item">
                                    <img src="{{ $about->photoUrl($photo['image']) }}" alt="" loading="lazy">
                                    <input type="hidden" name="photos[{{ $index }}][image]" value="{{ $photo['image'] }}">
                                    <label class="homepage-form-field"><span>Caption</span><input name="photos[{{ $index }}][caption]" value="{{ old("photos.$index.caption", $photo['caption'] ?? '') }}" maxlength="80"></label>
                                    <label class="china-travellers-remove"><input type="checkbox" name="photos[{{ $index }}][remove]" value="1"> Remove</label>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="homepage-field-hint">No photos yet. The slideshow appears on the website once you add some.</p>
                    @endif

                    <label class="homepage-form-field china-travellers-upload">
                        <span>Add photos <small class="homepage-field-hint">JPG, PNG or WebP, up to 5 MB each. Landscape photos at least 1200px wide look best.</small></span>
                        <input type="file" name="new_photos[]" multiple accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                    </label>

                    <div class="china-about-editor-actions">
                        <a class="admin-secondary-button" href="{{ route('china-muslim') }}#china-moments-title" target="_blank" rel="noreferrer">View on website ↗</a>
                        <button class="admin-primary-button" type="submit">Save photos <span aria-hidden="true">→</span></button>
                    </div>
                </form>
            @endif
        </main>

        <footer class="homepage-editor-footer">Smart Holidays · Website content manager</footer>
    </div>
</body>
</html>
