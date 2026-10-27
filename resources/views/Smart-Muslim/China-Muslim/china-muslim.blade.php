<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#ffffff">
    <meta name="description" content="China Muslim package tours and series departures by Smart Holidays.">
    <title>China Muslim Package Tours | Smart Holidays</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="island-page china-muslim-page">
    <a class="skip-link" href="#main">Skip to content</a>

    @include('partials.site-header')

    <main class="china-muslim-main" id="main">
        @php
            $heroPhotos = $packages->map(fn ($package) => ['url' => $package->imageUrl(), 'alt' => $package->title])->unique('url')->take(3)->values();
            $shortestTrip = $packages->min('days');
            $longestTrip = $packages->max('days');
            $chinaWhatsappUrl = 'https://wa.me/60165500590?text='.rawurlencode('Hi, I would like to ask about China Muslim group tours.');
        @endphp
        <header class="china-hero">
            <div class="china-hero__text">
                <p class="china-muslim-kicker">GALLERY SERIES DEPARTURES</p>
                <h1>CHINA MUSLIM PACKAGE TOURS</h1>
                <p class="china-hero__lead">Group journeys across China planned for Muslim travellers, with halal meals and time for prayer along the way.</p>
                @if ($packages->isNotEmpty())
                    <dl class="china-hero__stats">
                        <div><dt>Packages</dt><dd>{{ $packages->count() }}</dd></div>
                        <div><dt>From</dt><dd>RM{{ number_format($packages->min('price')) }}</dd></div>
                        <div><dt>Duration</dt><dd>{{ $shortestTrip === $longestTrip ? $shortestTrip : $shortestTrip.'–'.$longestTrip }} days</dd></div>
                    </dl>
                @endif
                <div class="china-hero__actions">
                    <a class="china-hero__primary" href="#china-series-title">View packages <span aria-hidden="true">↓</span></a>
                    @if ($about)
                        <button class="china-hero__secondary" type="button" data-about-open="china-about-modal"><span aria-hidden="true">🌏</span> About China</button>
                    @endif
                    <a class="china-hero__secondary" href="{{ $chinaWhatsappUrl }}" target="_blank" rel="noreferrer">Ask on WhatsApp <span aria-hidden="true">↗</span></a>
                </div>
            </div>
            @if ($heroPhotos->isNotEmpty())
                <div class="china-hero__collage" aria-hidden="true">
                    @foreach ($heroPhotos as $photo)
                        <img src="{{ $photo['url'] }}" alt="" loading="{{ $loop->first ? 'eager' : 'lazy' }}">
                    @endforeach
                </div>
            @endif
        </header>

        <ul class="china-perks" aria-label="Why travel with us">
            <li><span aria-hidden="true">🍽️</span> Halal meals</li>
            <li><span aria-hidden="true">🕌</span> Mosque visits</li>
            <li><span aria-hidden="true">🧳</span> Flights &amp; hotels arranged</li>
            <li><span aria-hidden="true">👥</span> Group departures</li>
        </ul>

        <section class="china-muslim-series" aria-labelledby="china-series-title">
            <div class="china-series-heading">
                <div>
                    <p class="china-series-heading__kicker">Choose your journey</p>
                    <h2 id="china-series-title">SERIES DEPARTURE CHINA MUSLIM</h2>
                </div>
                @auth
                    @if (auth()->user()->is_admin)
                        <a class="china-card-new" href="{{ route('admin.china-muslim.create') }}">
                            <span aria-hidden="true">＋</span> New
                        </a>
                    @endif
                @endauth
            </div>

            @if ($packages->isEmpty())
                <p class="china-muslim-empty">Series departures will be announced soon.</p>
            @else
                @php
                    $packageFilters = collect($packages->contains('is_hot', true) ? ['hot' => '🔥 Hot'] : [])
                        ->merge($packages->pluck('days')->unique()->sort()->mapWithKeys(fn (int $days): array => [$days.'d' => $days.' days']));
                @endphp
                <div class="focus-tours__toolbar china-series-toolbar" data-tour-finder data-item-singular="package" data-item-plural="packages">
                    <label class="focus-tours__search">
                        <span class="visually-hidden">Search packages</span>
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="2"/><path d="m20 20-3.5-3.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                        <input type="search" placeholder="Search cities or packages…" autocomplete="off" data-tour-search>
                    </label>
                    @if ($packageFilters->count() > 1)
                        <div class="focus-tours__filters" role="group" aria-label="Filter packages">
                            <button type="button" aria-pressed="true" data-tour-filter="">All</button>
                            @foreach ($packageFilters as $filterKey => $filterLabel)
                                <button type="button" aria-pressed="false" data-tour-filter="{{ $filterKey }}">{{ $filterLabel }}</button>
                            @endforeach
                        </div>
                    @endif
                    <p class="focus-tours__count" aria-live="polite" data-tour-count>{{ $packages->count() }} {{ \Illuminate\Support\Str::plural('package', $packages->count()) }}</p>
                </div>

                <div class="focus-tours__slider china-series-slider" data-card-slider>
                    <button class="focus-tours__arrow focus-tours__arrow--prev" type="button" aria-label="Previous packages" data-slider-prev hidden><span aria-hidden="true">‹</span></button>
                    <div class="focus-tours__track" data-slider-track>
                    @foreach ($packages as $package)
                        @php($info = $package->parsedDetails())
                        @php($hasDetails = $info['days'] !== [] || $info['sections'] !== [])
                        <article class="china-card" data-tour-card data-category="{{ implode('|', array_filter([$package->is_hot ? 'hot' : null, $package->days.'d'])) }}" data-search="{{ \Illuminate\Support\Str::lower($package->title.' '.$package->days.'d'.$package->nights.'n') }}">
                            <a class="china-card-link" href="{{ $package->link ?: '#contact' }}" @if ($hasDetails) data-china-open="china-modal-{{ $package->id }}" @elseif ($package->link) target="_blank" rel="noreferrer" @endif>
                                <div class="china-card-media">
                                    <img src="{{ $package->imageUrl() }}" alt="{{ $package->title }}" loading="lazy">
                                    @if ($package->is_hot)
                                        <span class="china-card-hot">🔥 HOT</span>
                                    @endif
                                    <span class="china-card-days">{{ $package->days }}D {{ $package->nights }}N</span>
                                </div>
                                <h3>{{ $package->title }}</h3>
                                <div class="china-card-footer">
                                    <div class="china-card-price-wrap">
                                        <small>From</small>
                                        <strong class="china-card-price">{{ $package->formattedPrice() }}</strong>
                                    </div>
                                    <span class="china-card-cta" aria-hidden="true">Details <span>→</span></span>
                                </div>
                            </a>
                            @include('partials.like-button', ['likeKey' => \App\Support\PackageLikes::key($package), 'likeLabel' => $package->title, 'likeClass' => 'package-like--card'])
                            @auth
                                @if (auth()->user()->is_admin)
                                    <a class="china-card-edit" href="{{ route('admin.china-muslim.edit', $package) }}">Edit</a>
                                @endif
                            @endauth

                            @if ($hasDetails)
                                @php($slides = collect($info['days'])->map(fn ($day) => ['type' => 'day', 'day' => $day]))
                                @foreach (['Departure Dates' => '📅', 'Accommodation' => '🏨', 'Mandatory Tours' => '🏛️', 'Shopping Stop' => '🛍️', 'Optional Tours' => '🎟️', 'Included' => '✅', 'Excluded' => '❌'] as $sectionTitle => $sectionIcon)
                                    @if (! empty($info['sections'][$sectionTitle]))
                                        @php($slides->push(['type' => 'list', 'title' => $sectionTitle, 'icon' => $sectionIcon, 'items' => $info['sections'][$sectionTitle]]))
                                    @endif
                                @endforeach
                                @if (! empty($info['sections']['Duration']) || ! empty($info['sections']['Language']) || $info['note'] !== '')
                                    @php($slides->push(['type' => 'info']))
                                @endif

                                <dialog class="china-modal" id="china-modal-{{ $package->id }}" aria-label="{{ $package->title }}">
                                    <div class="china-modal-shell">
                                        <button class="china-modal-close" type="button" data-china-close aria-label="Close">×</button>
                                        <header class="china-modal-head" style="background-image: linear-gradient(135deg, rgb(5 60 46 / 88%), rgb(15 138 102 / 70%)), url('{{ $package->imageUrl() }}')">
                                            <span class="china-modal-badge">{{ $package->days }}D {{ $package->nights }}N @if ($package->is_hot) · 🔥 HOT @endif</span>
                                            <h3>{{ $package->title }}</h3>
                                            <strong>From {{ $package->formattedPrice() }}</strong>
                                        </header>

                                        <div class="china-modal-viewport">
                                            <div class="china-modal-track">
                                                @foreach ($slides as $slide)
                                                    <section class="china-slide" aria-roledescription="slide">
                                                        @if ($slide['type'] === 'day')
                                                            <div class="china-slide-top">
                                                                <span class="china-slide-day">DAY {{ str_pad($slide['day']['number'], 2, '0', STR_PAD_LEFT) }}</span>
                                                                @if ($slide['day']['meals'] !== '')
                                                                    <span class="china-slide-meals">🍽️ {{ $slide['day']['meals'] }}</span>
                                                                @endif
                                                            </div>
                                                            <h4>{{ $slide['day']['title'] }}</h4>
                                                            <ul class="china-slide-timeline">
                                                                @foreach ($slide['day']['items'] as $item)
                                                                    <li>{{ $item }}</li>
                                                                @endforeach
                                                            </ul>
                                                        @elseif ($slide['type'] === 'list')
                                                            <div class="china-slide-top"><span class="china-slide-day">{{ $slide['icon'] }} {{ strtoupper($slide['title']) }}</span></div>
                                                            <ul class="china-slide-list china-slide-list-{{ \Illuminate\Support\Str::slug($slide['title']) }}">
                                                                @foreach ($slide['items'] as $item)
                                                                    <li>{{ $item }}</li>
                                                                @endforeach
                                                            </ul>
                                                        @else
                                                            <div class="china-slide-top"><span class="china-slide-day">ℹ️ GOOD TO KNOW</span></div>
                                                            <div class="china-slide-chips">
                                                                @foreach (['Duration' => '⏱️', 'Language' => '🗣️'] as $infoTitle => $infoIcon)
                                                                    @if (! empty($info['sections'][$infoTitle]))
                                                                        <div>
                                                                            <small>{{ $infoIcon }} {{ $infoTitle }}</small>
                                                                            <strong>{{ implode(' · ', $info['sections'][$infoTitle]) }}</strong>
                                                                        </div>
                                                                    @endif
                                                                @endforeach
                                                            </div>
                                                            @if ($info['note'] !== '')
                                                                <p class="china-slide-note">📝 {{ $info['note'] }}</p>
                                                            @endif
                                                        @endif
                                                    </section>
                                                @endforeach
                                            </div>
                                        </div>

                                        <footer class="china-modal-nav">
                                            <button type="button" class="china-modal-arrow" data-china-prev aria-label="Previous slide">←</button>
                                            <div class="china-modal-progress">
                                                <span class="china-modal-count" data-china-count></span>
                                                <div class="china-modal-dots"></div>
                                            </div>
                                            <button type="button" class="china-modal-arrow" data-china-next aria-label="Next slide">→</button>
                                        </footer>
                                        @if ($chinaBookingUrl = (new \App\Support\BookablePackage($package))->bookNowUrl())
                                            <a class="china-modal-book china-modal-book--online" href="{{ $chinaBookingUrl }}">Book now →</a>
                                        @endif
                                        <a class="china-modal-book" href="https://wa.me/60165500590?text={{ rawurlencode('Hi, I am interested in '.$package->title) }}" target="_blank" rel="noreferrer">Enquire on WhatsApp</a>
                                        <button class="china-modal-about" type="button" data-about-open="china-about-modal"
                                            @if ($aboutVideo = $package->aboutVideo()) data-about-video-type="{{ $aboutVideo['type'] }}" data-about-video-src="{{ $aboutVideo['src'] }}" data-about-video-title="{{ $package->title }}" @endif>🌏 About this place @if ($aboutVideo) <span class="china-modal-about-video-tag">▶ Video</span> @endif</button>
                                    </div>
                                </dialog>
                            @endif
                        </article>
                    @endforeach
                    </div>
                    <button class="focus-tours__arrow focus-tours__arrow--next" type="button" aria-label="Next packages" data-slider-next hidden><span aria-hidden="true">›</span></button>
                </div>
                <div class="focus-tours__dots" data-slider-dots hidden></div>

                <p class="focus-tours__empty" data-tour-empty hidden>
                    No packages match your search. <button type="button" data-tour-reset>Show all packages</button>
                </p>
            @endif

            <?php
                $aboutPhotos = filled($about?->gallery)
                    ? collect($about->gallery)->map(fn ($path) => ['url' => \Illuminate\Support\Facades\Storage::disk('public')->url($path), 'alt' => $about->title])
                    : $packages->map(fn ($package) => ['url' => $package->imageUrl(), 'alt' => $package->title])->unique('url')->take(8);
                $aboutPhotos = $aboutPhotos->values();
                $aboutFactIcons = ['🏙️', '🗣️', '💴', '🕘'];
            ?>
            @if ($about)
            <dialog class="china-modal china-about-modal" id="china-about-modal" aria-label="{{ $about->title }}">
                <div class="china-modal-shell">
                    <button class="china-modal-close" type="button" data-about-close aria-label="Close">×</button>
                    <header class="china-modal-head" @if ($aboutPhotos->isNotEmpty()) style="background-image: linear-gradient(135deg, rgb(5 60 46 / 88%), rgb(15 138 102 / 70%)), url('{{ $aboutPhotos[0]['url'] }}')" @endif>
                        <span class="china-modal-badge">🌏 {{ $about->badge }}</span>
                        <h3>{{ $about->title }}</h3>
                    </header>
                    <div class="china-about-body">
                        <div class="china-about-video" data-about-video hidden></div>
                        @if ($aboutPhotos->isNotEmpty())
                            <div class="china-about-gallery" data-about-gallery>
                                <img class="china-about-main" src="{{ $aboutPhotos[0]['url'] }}" alt="{{ $aboutPhotos[0]['alt'] }}" data-about-main>
                                <div class="china-about-thumbs">
                                    @foreach ($aboutPhotos as $photo)
                                        <button type="button" class="china-about-thumb{{ $loop->first ? ' is-active' : '' }}" data-about-thumb="{{ $photo['url'] }}" data-about-alt="{{ $photo['alt'] }}" aria-label="Show photo {{ $loop->iteration }}">
                                            <img src="{{ $photo['url'] }}" alt="" loading="lazy">
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        <p>{{ $about->intro }}</p>
                        <div class="china-slide-chips china-about-facts">
                            @foreach ($about->facts as $fact)
                                <div><small>{{ $aboutFactIcons[$loop->index] ?? '•' }} {{ $fact['label'] }}</small><strong>{{ $fact['value'] }}</strong></div>
                            @endforeach
                        </div>
                        <p>{{ $about->body }}</p>
                        <p class="china-slide-note">📝 {{ $about->note }}</p>
                    </div>
                    <button class="china-modal-about" type="button" data-about-close>Close</button>
                </div>
            </dialog>
            @endif
        </section>

        @include('Smart-Muslim.China-Muslim.partials.traveller-moments', ['about' => $about])

        <section class="china-cta" aria-labelledby="china-cta-title">
            <div>
                <h2 id="china-cta-title">Planning a group trip to China?</h2>
                <p>Tell us your dates and group size, and our team will suggest the best departure for you.</p>
            </div>
            <a href="{{ $chinaWhatsappUrl }}" target="_blank" rel="noreferrer">Chat on WhatsApp <span aria-hidden="true">↗</span></a>
        </section>
    </main>

    <footer class="site-footer">
        <div class="footer-inner">
            <a class="footer-logo" href="{{ route('home') }}" aria-label="Smart Holidays home">
                <img src="{{ asset('images/smart-holidays-logo.png') }}" alt="Smart Holidays">
            </a>
            <div class="footer-company">
                <h2>ROYSTAR TRAVEL &amp; TOURS SDN BHD</h2>
                <p>No.16A &amp; 16B, Lorong Brunei 3,<br>Off Jalan Pudu, 55100 Kuala Lumpur,<br>WPKL, Malaysia.</p>
                <a href="tel:+60321430188">Tour: +603 2143 0188 / 0288</a>
                <a href="https://wa.me/60165500590" target="_blank" rel="noreferrer">WhatsApp: +6016 550 0590</a>
                <a href="mailto:tour@roystartravel.com.my">tour@roystartravel.com.my</a>
            </div>
            <div class="footer-links">
                <h2>Quick Links</h2>
                <a href="{{ route('smart-muslim') }}">Smart Muslim Home</a>
                <a href="{{ route('china-muslim') }}">China Muslim</a>
                <a href="{{ route('island-honeymoon') }}">Island &amp; Honeymoon</a>
                <a href="{{ route('indonesia') }}">Indonesia destinations</a>
                <a href="{{ route('thailand') }}">Thailand destinations</a>
                <a href="#contact">Contact Us</a>
                <a href="{{ route('privacy-policy') }}">Privacy Policy</a>
            </div>
            <div class="footer-brands" aria-label="Our travel brands">
                <h2>Our brands</h2>
                <a href="{{ route('smart-muslim') }}" class="footer-brand-badge"><strong>SMART</strong><span>MUSLIM</span></a>
                <a href="{{ route('home') }}" class="footer-brand-badge footer-brand-main"><strong>SMART</strong><span>HOLIDAYS</span></a>
            </div>
        </div>
        <div class="footer-bottom">
            © {{ date('Y') }} Smart Holidays. All rights reserved.
        </div>
    </footer>
</body>
</html>
