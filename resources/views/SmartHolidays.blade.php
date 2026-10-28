<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#14345f">
    <meta name="description" content="{{ $page->meta_description }}">
    <title>{{ $page->meta_title }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>@view-transition { navigation: auto; }</style>
</head>
<body class="smart-home">
    <a class="skip-link" href="#main">Skip to content</a>

    @include('partials.site-header')

    <a class="admin-portal-fab" href="{{ route('login') }}" aria-label="Open admin login" title="Admin login">
        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M4 20V7.5L12 4l8 3.5V20H4Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
            <path d="M9 20v-5a3 3 0 0 1 6 0v5M8 9h.01M12 9h.01M16 9h.01" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
        </svg>
    </a>

    <main id="main">
        <section class="sh-hero" id="explore" aria-labelledby="smart-intro-title">
            <img class="sh-hero-image" src="{{ $page->imageUrl($page->hero_image) }}" alt="{{ $page->hero_image_alt }}" width="1920" height="600" fetchpriority="high">
            <div class="sh-hero-overlay" aria-hidden="true"></div>
            <div class="sh-hero-content">
                <span class="sh-eyebrow">{{ $page->hero_eyebrow }}</span>
                <h1 id="smart-intro-title">{{ $page->hero_title }}</h1>
                <p>{{ $page->hero_body }}</p>
                <div class="sh-hero-actions">
                    <a class="sh-btn sh-btn-primary" href="{{ $page->whatsappUrl() }}" target="_blank" rel="noreferrer">{{ $page->hero_cta_label }} <span aria-hidden="true">↗</span></a>
                    <a class="sh-btn sh-btn-ghost" href="#brands">{{ $page->hero_secondary_label }} <span aria-hidden="true">↓</span></a>
                </div>
            </div>
            <ul class="sh-stats" aria-label="Why travellers choose us">
                @foreach ($page->stats as $stat)
                    <li><strong>{{ $stat['title'] }}</strong><span>{{ $stat['body'] }}</span></li>
                @endforeach
            </ul>
        </section>

        <section class="sh-services" aria-labelledby="sh-services-title">
            <div class="sh-services-inner">
                <div class="island-section-heading">
                    <span class="island-section-label">{{ $page->services_eyebrow }}</span>
                    <h2 id="sh-services-title">{{ $page->services_heading }}</h2>
                </div>
                <div class="sh-service-grid">
                    <article class="sh-service">
                        <div class="sh-service-topline">
                            <span class="sh-service-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="9"/>
                                    <path d="M3.5 9h17M3.5 15h17M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18"/>
                                </svg>
                            </span>
                            <span class="sh-service-number" aria-hidden="true">01</span>
                        </div>
                        <h3>{{ $page->services[0]['title'] }}</h3>
                        <p>{{ $page->services[0]['body'] }}</p>
                    </article>
                    <article class="sh-service">
                        <div class="sh-service-topline">
                            <span class="sh-service-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="9"/>
                                    <path d="m15.8 8.2-2.5 5.1-5.1 2.5 2.5-5.1 5.1-2.5Z"/>
                                    <circle cx="12" cy="12" r="1"/>
                                </svg>
                            </span>
                            <span class="sh-service-number" aria-hidden="true">02</span>
                        </div>
                        <h3>{{ $page->services[1]['title'] }}</h3>
                        <p>{{ $page->services[1]['body'] }}</p>
                    </article>
                    <article class="sh-service">
                        <div class="sh-service-topline">
                            <span class="sh-service-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24">
                                    <path d="M12 20v-8"/>
                                    <path d="M12 12c-1-3-3.5-4.5-6.5-4.5 1 3 3.3 4.5 6.5 4.5ZM12 12c1-3 3.5-4.5 6.5-4.5-1 3-3.3 4.5-6.5 4.5ZM12 12c0-3.5 1.5-6 4-7.5.5 3.3-.8 6-4 7.5Z"/>
                                    <path d="M5 20c2-1.2 4.3-1.2 7 0 2.7-1.2 5-1.2 7 0"/>
                                </svg>
                            </span>
                            <span class="sh-service-number" aria-hidden="true">03</span>
                        </div>
                        <h3>{{ $page->services[2]['title'] }}</h3>
                        <p>{{ $page->services[2]['body'] }}</p>
                    </article>
                    <article class="sh-service">
                        <div class="sh-service-topline">
                            <span class="sh-service-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24">
                                    <path d="m21 3-7.2 18-3.4-7.4L3 10.2 21 3Z"/>
                                    <path d="m10.4 13.6 4.4-4.4"/>
                                </svg>
                            </span>
                            <span class="sh-service-number" aria-hidden="true">04</span>
                        </div>
                        <h3>{{ $page->services[3]['title'] }}</h3>
                        <p>{{ $page->services[3]['body'] }}</p>
                    </article>
                </div>
            </div>
        </section>

        @php
            $journeySlides = array_map(fn (array $slide): array => [...$slide, 'image' => $page->imageUrl($slide['image']), 'video' => filled($slide['video'] ?? '') ? $page->imageUrl($slide['video']) : ''], $page->journey_slides);
        @endphp
        <section class="sh-journeys" aria-label="Featured travel inspiration">
            <div class="sh-cine" data-sh-journey-carousel role="region" aria-roledescription="carousel" aria-label="Featured travel destinations">
                <div class="sh-cine-stage">
                    @foreach ($journeySlides as $journeySlide)
                        <article class="sh-cine-slide{{ $loop->first ? ' is-active' : '' }}" aria-roledescription="slide" aria-label="{{ $loop->iteration }} of {{ count($journeySlides) }}" data-sh-journey-slide @unless ($loop->first) aria-hidden="true" inert @endunless>
                            <div class="sh-cine-media">
                                <img src="{{ $journeySlide['image'] }}" alt="{{ $journeySlide['alt'] }}" @if ($loop->first) fetchpriority="high" @else loading="lazy" @endif decoding="async">
                                @if ($journeySlide['video'] !== '')
                                    <video class="sh-journey-video" src="{{ $journeySlide['video'] }}" poster="{{ $journeySlide['image'] }}" muted loop playsinline preload="{{ $loop->first ? 'auto' : 'none' }}" @if ($loop->first) autoplay @endif aria-hidden="true" data-sh-journey-video></video>
                                @endif
                            </div>
                            <span class="sh-cine-number" aria-hidden="true">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                            <div class="sh-cine-caption">
                                <span class="sh-cine-eyebrow">{{ $journeySlide['eyebrow'] }}</span>
                                <h3>@foreach (preg_split('/\s+/', trim($journeySlide['title'])) as $word)<span class="sh-cine-word" style="--word: {{ $loop->index }}"><span>{{ $word }}</span></span> @endforeach</h3>
                                <p>{{ $journeySlide['body'] }}</p>
                                <a class="sh-cine-cta" href="{{ $journeySlide['link_url'] }}">{{ $journeySlide['link_label'] }} <span aria-hidden="true">↗</span></a>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="sh-cine-hud">
                    <div class="sh-cine-progress" role="group" aria-label="Choose a destination">
                        @foreach ($journeySlides as $journeySlide)
                            <button type="button" aria-label="Show destination {{ $loop->iteration }}: {{ $journeySlide['title'] }}" @if ($loop->first) aria-current="true" @endif data-sh-journey-select data-sh-journey-index="{{ $loop->index }}">
                                <span class="sh-cine-progress-label"><b>{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</b>{{ filled($journeySlide['thumb'] ?? '') ? $journeySlide['thumb'] : $journeySlide['title'] }}</span>
                                <span class="sh-cine-progress-track"><i></i></span>
                            </button>
                        @endforeach
                    </div>
                    <div class="sh-cine-nav">
                        <button type="button" class="sh-cine-next-card" data-sh-journey-next-card aria-label="Next destination">
                            <img src="{{ $journeySlides[1]['image'] ?? $journeySlides[0]['image'] }}" alt="" loading="lazy" data-sh-journey-next-image>
                            <span><small>Next up</small><strong data-sh-journey-next-title>{{ $journeySlides[1]['title'] ?? $journeySlides[0]['title'] }}</strong></span>
                        </button>
                        <button type="button" class="sh-cine-button" data-sh-journey-previous aria-label="Previous destination">‹</button>
                        <button type="button" class="sh-cine-button" data-sh-journey-toggle aria-label="Pause slideshow" aria-pressed="false"><span aria-hidden="true">❚❚</span></button>
                        <button type="button" class="sh-cine-button" data-sh-journey-next aria-label="Next destination">›</button>
                    </div>
                </div>
            </div>
        </section>

        <div class="sh-divider" aria-hidden="true"><span></span></div>

        @if ($popularTours !== [])
            @php
                $popularTourActiveIndex = min(2, count($popularTours) - 1);
            @endphp
            <section class="popular-tours{{ filled($page->popular_video) ? ' has-video' : '' }}" aria-labelledby="popular-tours-title">
                @if (filled($page->popular_video))
                    <video class="popular-tours-video" src="{{ $page->imageUrl($page->popular_video) }}" autoplay muted loop playsinline preload="metadata" aria-hidden="true" data-background-video></video>
                @endif
                <div class="popular-tours-heading">
                    <span class="island-section-label">{{ $page->popular_eyebrow }}</span>
                    <h2 id="popular-tours-title">{{ $page->popular_heading }}</h2>
                    <p>{{ $page->popular_body }}</p>
                </div>
                <div class="popular-tour-carousel" data-popular-tour-carousel data-active-index="{{ $popularTourActiveIndex }}" aria-label="Popular tours">
                    <div class="popular-tour-viewport">
                        <div class="popular-tour-track">
                            @foreach ($popularTours as $tour)
                                <article class="popular-tour-card{{ $loop->index === $popularTourActiveIndex ? ' is-active' : '' }}" data-popular-tour-card>
                                    <a href="{{ $tour['href'] }}">
                                        <div class="popular-tour-image">
                                                <img src="{{ $tour['image'] }}" alt="{{ $tour['title'] }}, {{ $tour['country'] }}" loading="lazy" decoding="async">
                                            <span>{{ $tour['country'] }}</span>
                                            <em class="popular-tour-brand">Smart Muslim</em>
                                        </div>
                                        <div class="popular-tour-copy">
                                            <h3>{{ $tour['title'] }}</h3>
                                            <p>{{ $tour['tagline'] }}</p>
                                            <strong>Explore tour <span aria-hidden="true">↗</span></strong>
                                        </div>
                                    </a>
                                    @include('partials.like-button', ['likeKey' => $tour['likeKey'], 'likeLabel' => $tour['title'], 'likeClass' => 'package-like--popular'])
                                </article>
                            @endforeach
                        </div>
                    </div>
                    <div class="popular-tour-footer">
                        <span aria-hidden="true"></span>
                        <div class="popular-tour-dots" role="group" aria-label="Choose a popular tour">
                            @foreach ($popularTours as $tour)
                                <button type="button" data-popular-tour-dot aria-label="Show {{ $tour['title'] }}" @if ($loop->index === $popularTourActiveIndex) aria-current="true" @endif></button>
                            @endforeach
                        </div>
                        <a class="popular-tour-all" href="{{ route('tour-package.show', 'series-departures-2026') }}">View all Smart Muslim Tours <span aria-hidden="true">→</span></a>
                    </div>
                </div>
            </section>
        @endif

        <div class="sh-divider" aria-hidden="true"><span></span></div>

        {{-- Placeholder Smart Holidays cards until real group, ground and domestic packages are linked here. --}}
        @php
            $holidayTours = [
                ['title' => 'Seoul & Nami Island', 'country' => 'South Korea', 'tagline' => 'Palaces, markets and autumn colours in 6 days.', 'image' => asset('images/series/seoul-2.jpg'), 'href' => route('group-tours')],
                ['title' => 'Melbourne & Great Ocean Road', 'country' => 'Australia', 'tagline' => 'City laneways and a coastline drive to remember.', 'image' => asset('images/series/melbourne-1.jpg'), 'href' => route('group-tours')],
                ['title' => 'Maldives Water Villas', 'country' => 'Maldives', 'tagline' => 'Overwater stays, reef snorkelling and slow sunsets.', 'image' => asset('images/island-honeymoon/maldives-water-villas.jpg'), 'href' => route('island-honeymoon')],
                ['title' => 'Medan & Lake Toba', 'country' => 'Indonesia', 'tagline' => 'Highland air, waterfalls and the great crater lake.', 'image' => asset('images/series/medan-1.jpg'), 'href' => route('ground-tours')],
                ['title' => 'Krabi Island Hopping', 'country' => 'Thailand', 'tagline' => 'Longtail boats, limestone cliffs and white sand.', 'image' => asset('images/island-honeymoon/krabi-longtail-boats.jpg'), 'href' => route('ground-tours')],
                ['title' => 'Kota Kinabalu Getaway', 'country' => 'Malaysia', 'tagline' => 'Island parks, sunset seafood and Mount Kinabalu views.', 'image' => asset('images/mice-gallery/mbmb-kota-kinabalu-2025.jpg'), 'href' => route('domestic-tours')],
            ];
            $holidayTourActiveIndex = 2;
        @endphp
        <section class="popular-tours popular-tours--holidays{{ filled($page->holidays_popular_video) || filled($page->holidays_popular_image) ? ' has-video' : '' }}" aria-labelledby="popular-holidays-title">
            @if (filled($page->holidays_popular_video))
                <video class="popular-tours-video" src="{{ $page->imageUrl($page->holidays_popular_video) }}" @if (filled($page->holidays_popular_image)) poster="{{ $page->imageUrl($page->holidays_popular_image) }}" @endif autoplay muted loop playsinline preload="metadata" aria-hidden="true" data-background-video></video>
            @elseif (filled($page->holidays_popular_image))
                <img class="popular-tours-video" src="{{ $page->imageUrl($page->holidays_popular_image) }}" alt="" loading="lazy" decoding="async" aria-hidden="true">
            @endif
            <div class="popular-tours-heading">
                <span class="island-section-label">SMART HOLIDAYS PACKAGES</span>
                <h2 id="popular-holidays-title">Popular in Smart Holidays</h2>
                <p>Group, ground and domestic journeys our travellers love most.</p>
            </div>
            <div class="popular-tour-carousel" data-popular-tour-carousel data-active-index="{{ $holidayTourActiveIndex }}" aria-label="Popular Smart Holidays tours">
                <div class="popular-tour-viewport">
                    <div class="popular-tour-track">
                        @foreach ($holidayTours as $tour)
                            <article class="popular-tour-card{{ $loop->index === $holidayTourActiveIndex ? ' is-active' : '' }}" data-popular-tour-card>
                                <a href="{{ $tour['href'] }}">
                                    <div class="popular-tour-image">
                                        <img src="{{ $tour['image'] }}" alt="{{ $tour['title'] }}, {{ $tour['country'] }}" loading="lazy" decoding="async">
                                        <span>{{ $tour['country'] }}</span>
                                        <em class="popular-tour-brand">Smart Holidays</em>
                                    </div>
                                    <div class="popular-tour-copy">
                                        <h3>{{ $tour['title'] }}</h3>
                                        <p>{{ $tour['tagline'] }}</p>
                                        <strong>Explore tour <span aria-hidden="true">↗</span></strong>
                                    </div>
                                </a>
                            </article>
                        @endforeach
                    </div>
                </div>
                <div class="popular-tour-footer">
                    <span aria-hidden="true"></span>
                    <div class="popular-tour-dots" role="group" aria-label="Choose a popular Smart Holidays tour">
                        @foreach ($holidayTours as $tour)
                            <button type="button" data-popular-tour-dot aria-label="Show {{ $tour['title'] }}" @if ($loop->index === $holidayTourActiveIndex) aria-current="true" @endif></button>
                        @endforeach
                    </div>
                    <a class="popular-tour-all" href="{{ route('group-tours') }}">View all Smart Holidays Tours <span aria-hidden="true">→</span></a>
                </div>
            </div>
        </section>

        <div class="sh-divider" aria-hidden="true"><span></span></div>

        @if (filled($page->poster_slides))
            <section class="sh-posters" aria-labelledby="sh-posters-title">
                <div class="sh-posters-backdrop" aria-hidden="true">
                    <span class="sh-posters-orb sh-posters-orb-a"></span>
                    <span class="sh-posters-orb sh-posters-orb-b"></span>
                    <span class="sh-posters-ring"></span>
                    <span class="sh-posters-marquee"><span>{{ $page->posters_heading }} ✦ {{ $page->posters_heading }} ✦ {{ $page->posters_heading }} ✦&nbsp;</span><span>{{ $page->posters_heading }} ✦ {{ $page->posters_heading }} ✦ {{ $page->posters_heading }} ✦&nbsp;</span></span>
                </div>
                <div class="sh-posters-heading">
                    <span class="sh-posters-eyebrow">{{ $page->posters_eyebrow }}</span>
                    <h2 id="sh-posters-title">{{ $page->posters_heading }}</h2>
                </div>
                <div class="sh-poster-carousel" data-sh-poster-carousel role="region" aria-roledescription="carousel" aria-label="{{ $page->posters_heading }}">
                    <div class="sh-poster-stage">
                        @foreach ($page->poster_slides as $poster)
                            <article class="sh-poster{{ $loop->first ? ' is-active' : '' }}" aria-roledescription="slide" aria-label="{{ $loop->iteration }} of {{ count($page->poster_slides) }}" data-sh-poster @unless ($loop->first) aria-hidden="true" @endunless>
                                <a class="sh-poster-card" href="{{ $poster['link_url'] }}" @unless ($loop->first) tabindex="-1" @endunless>
                                    <span class="sh-poster-frame">
                                        <img src="{{ $page->imageUrl($poster['image']) }}" alt="{{ $poster['alt'] }}" loading="lazy" decoding="async">
                                    </span>
                                    <span class="sh-poster-info">
                                        @if (filled($poster['label']))
                                            <span class="sh-poster-label">{{ $poster['label'] }}</span>
                                        @endif
                                        <strong>{{ $poster['title'] }}</strong>
                                        @if (filled($poster['price']))
                                            <span class="sh-poster-price">{{ $poster['price'] }}</span>
                                        @endif
                                        <span class="sh-poster-cta">View details <span aria-hidden="true">→</span></span>
                                    </span>
                                </a>
                            </article>
                        @endforeach
                    </div>
                    <div class="sh-poster-controls">
                        <button type="button" data-sh-poster-previous aria-label="Previous poster">‹</button>
                        <div class="sh-poster-progress" aria-hidden="true">
                            <span class="sh-poster-count"><b data-sh-poster-current>01</b> / {{ str_pad((string) count($page->poster_slides), 2, '0', STR_PAD_LEFT) }}</span>
                            <span class="sh-poster-bar"><i data-sh-poster-bar></i></span>
                        </div>
                        <button type="button" data-sh-poster-toggle aria-label="Pause slideshow" aria-pressed="false"><span aria-hidden="true">❚❚</span></button>
                        <button type="button" data-sh-poster-next aria-label="Next poster">›</button>
                    </div>
                </div>
            </section>
        @endif

        <div class="sh-closing">
        <section class="smart-brands" id="brands" aria-labelledby="smart-brands-title">
            <div class="island-section-heading">
                <span class="island-section-label">{{ $page->brands_eyebrow }}</span>
                <h2 id="smart-brands-title">{{ $page->brands_heading }}</h2>
            </div>
            <div class="brand-tiles">
                <article class="brand-tile brand-tile--holidays">
                    <img class="brand-tile__photo" src="https://images.unsplash.com/photo-1436491865332-7a61a109cc05?auto=format&fit=crop&w=1200&q=80" alt="" loading="lazy">
                    <div class="brand-tile__body">
                        <div class="brand-tile__top">
                            <span class="brand-tile__logo"><img src="{{ asset('images/smart-holidays-logo.png') }}" alt="Smart Holidays logo" loading="lazy"></span>
                            <span class="brand-tile__index" aria-hidden="true">01</span>
                        </div>
                        <span class="brand-tile__kicker">Holidays for every traveller</span>
                        <h3>Smart Holidays</h3>
                        <p>Group tours, ground tours and domestic travel crafted for every kind of traveller.</p>
                        <ul class="brand-tile__links" aria-label="Smart Holidays tours">
                            <li><a href="{{ route('group-tours') }}">Group Tours <span aria-hidden="true">→</span></a></li>
                            <li><a href="{{ route('ground-tours') }}">Ground Destination <span aria-hidden="true">→</span></a></li>
                            <li><a href="{{ route('domestic-tours') }}">Domestic Tour <span aria-hidden="true">→</span></a></li>
                        </ul>
                        <a class="brand-tile__cta" href="https://wa.me/60165500590?text=Hi%2C%20I%27d%20like%20to%20ask%20about%20Smart%20Holidays%20tours." target="_blank" rel="noreferrer">Enquire on WhatsApp <span aria-hidden="true">↗</span></a>
                    </div>
                </article>
                <article class="brand-tile brand-tile--muslim">
                    <img class="brand-tile__photo" src="https://images.unsplash.com/photo-1519817650390-64a93db51149?auto=format&fit=crop&w=1200&q=80" alt="" loading="lazy">
                    <div class="brand-tile__body">
                        <div class="brand-tile__top">
                            <span class="brand-tile__logo"><img src="{{ asset('images/smart-muslim-logo.png') }}" alt="Smart Muslim logo" loading="lazy"></span>
                            <span class="brand-tile__index" aria-hidden="true">02</span>
                        </div>
                        <span class="brand-tile__kicker">Muslim-friendly travel</span>
                        <h3>Smart Muslim Tour</h3>
                        <p>Muslim-friendly private packages, series tours and island &amp; honeymoon getaways.</p>
                        <ul class="brand-tile__links" aria-label="Smart Muslim tours">
                            <li><a href="{{ route('tour-package-private') }}">Private Packages <span aria-hidden="true">→</span></a></li>
                            <li><a href="{{ route('tour-package.show', 'series-departures-2026') }}">Series Tours <span aria-hidden="true">→</span></a></li>
                            <li><a href="{{ route('island-honeymoon') }}">Island &amp; Honeymoon <span aria-hidden="true">→</span></a></li>
                        </ul>
                        <a class="brand-tile__cta" href="{{ route('smart-muslim') }}">Explore Smart Muslim <span aria-hidden="true">→</span></a>
                    </div>
                </article>
            </div>
        </section>

        @if (filled($page->reel_slides))
            @php
                $reelStartIndex = intdiv(count($page->reel_slides) - 1, 2);
            @endphp
            <section class="sh-reels" aria-labelledby="sh-reels-title" data-sh-reels data-start-index="{{ $reelStartIndex }}" data-reveal>
                <div class="sh-reels-head">
                    <div class="sh-reels-heading">
                        <span class="island-section-label">{{ $page->reels_eyebrow }}</span>
                        <h2 id="sh-reels-title">{{ $page->reels_heading }}</h2>
                    </div>
                    <div class="sh-reels-controls">
                        <span class="sh-reels-count" aria-hidden="true"><b data-sh-reels-current>{{ str_pad((string) ($reelStartIndex + 1), 2, '0', STR_PAD_LEFT) }}</b> / {{ str_pad((string) count($page->reel_slides), 2, '0', STR_PAD_LEFT) }}</span>
                        <button type="button" data-sh-reels-previous aria-label="Previous video"><span aria-hidden="true">‹</span></button>
                        <button type="button" data-sh-reels-toggle aria-label="Pause videos" aria-pressed="false"><span aria-hidden="true">❚❚</span></button>
                        <button type="button" data-sh-reels-next aria-label="Next video"><span aria-hidden="true">›</span></button>
                    </div>
                </div>
                <ol class="sh-reels-track" data-sh-reels-track aria-label="{{ $page->reels_heading }}">
                    @foreach ($page->reel_slides as $reel)
                        <li class="sh-reel{{ $loop->index === $reelStartIndex ? ' is-active' : '' }}" data-sh-reel>
                            <figure class="sh-reel-frame">
                                <video class="sh-reel-video" src="{{ $page->imageUrl($reel['video']) }}" @if (filled($reel['image'])) poster="{{ $page->imageUrl($reel['image']) }}" @endif muted playsinline preload="{{ $loop->index === $reelStartIndex ? 'metadata' : 'none' }}" aria-hidden="true" data-sh-reel-video></video>
                                <span class="sh-reel-progress" aria-hidden="true"><i></i></span>
                                <span class="sh-reel-play" aria-hidden="true">▶</span>
                                <button type="button" class="sh-reel-expand" data-sh-reel-open aria-label="Watch {{ $reel['title'] }} full screen" @unless ($loop->index === $reelStartIndex) tabindex="-1" @endunless>
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7"/></svg>
                                </button>
                                <figcaption class="sh-reel-info">
                                    @if (filled($reel['label']))
                                        <span class="sh-reel-label">{{ $reel['label'] }}</span>
                                    @endif
                                    <strong>{{ $reel['title'] }}</strong>
                                    @if (filled($reel['link_url']))
                                        <a class="sh-reel-link" href="{{ $reel['link_url'] }}">Explore <span aria-hidden="true">→</span></a>
                                    @endif
                                </figcaption>
                            </figure>
                        </li>
                    @endforeach
                </ol>
                <dialog class="sh-reel-viewer" data-sh-reel-viewer aria-labelledby="sh-reel-viewer-title">
                    <div class="sh-reel-viewer-glow" aria-hidden="true" data-sh-reel-viewer-glow></div>
                    @if (count($page->reel_slides) > 1)
                        <button type="button" class="sh-reel-viewer-nav sh-reel-viewer-nav--previous" data-sh-reel-viewer-previous aria-label="Previous video"><span aria-hidden="true">‹</span></button>
                    @endif
                    <div class="sh-reel-viewer-stage" data-sh-reel-viewer-stage>
                        <video class="sh-reel-viewer-video" playsinline preload="auto" data-sh-reel-viewer-video></video>
                        <span class="sh-reel-viewer-progress" aria-hidden="true"><i data-sh-reel-viewer-bar></i></span>
                        <div class="sh-reel-viewer-top">
                            <span class="sh-reel-viewer-count" aria-hidden="true"><b data-sh-reel-viewer-current>01</b> / {{ str_pad((string) count($page->reel_slides), 2, '0', STR_PAD_LEFT) }}</span>
                            <button type="button" class="sh-reel-viewer-button" data-sh-reel-viewer-sound aria-pressed="false" aria-label="Mute sound">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M11 5 6 9H2v6h4l5 4V5Z"/><path class="sh-reel-viewer-sound-on" d="M15.5 8.5a5 5 0 0 1 0 7M19 5a10 10 0 0 1 0 14"/><path class="sh-reel-viewer-sound-off" d="m22 9-6 6M16 9l6 6"/></svg>
                            </button>
                            <button type="button" class="sh-reel-viewer-button" data-sh-reel-viewer-close aria-label="Close video" autofocus><span aria-hidden="true">✕</span></button>
                        </div>
                        <span class="sh-reel-viewer-paused" aria-hidden="true">▶</span>
                        <div class="sh-reel-viewer-info">
                            <span class="sh-reel-label" data-sh-reel-viewer-label></span>
                            <strong id="sh-reel-viewer-title" data-sh-reel-viewer-title></strong>
                            <a class="sh-reel-viewer-link" href="#" data-sh-reel-viewer-link>Explore <span aria-hidden="true">→</span></a>
                        </div>
                    </div>
                    @if (count($page->reel_slides) > 1)
                        <button type="button" class="sh-reel-viewer-nav sh-reel-viewer-nav--next" data-sh-reel-viewer-next aria-label="Next video"><span aria-hidden="true">›</span></button>
                    @endif
                </dialog>
            </section>
        @endif

        <section class="sh-cta" aria-labelledby="sh-cta-title">
            <h2 id="sh-cta-title">{{ $page->cta_heading }}</h2>
            <p>{{ $page->cta_body }}</p>
            <a class="sh-btn sh-btn-primary" href="{{ $page->whatsappUrl() }}" target="_blank" rel="noreferrer">{{ $page->cta_label }} <span aria-hidden="true">↗</span></a>
        </section>
        </div>
    </main>

    <footer class="site-footer" id="contact">
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
                <a href="{{ route('smart-muslim') }}">Smart Muslim Tour</a>
                <a href="{{ route('island-honeymoon') }}">Island &amp; Honeymoon</a>
                <a href="{{ route('indonesia') }}">Indonesia</a>
                <a href="{{ route('thailand') }}">Thailand</a>
                <a href="{{ route('vietnam') }}">Vietnam</a>
                <a href="{{ route('philippines') }}">Philippines</a>
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
