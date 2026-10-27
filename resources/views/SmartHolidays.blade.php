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
            <div class="sh-journey-carousel" data-sh-journey-carousel role="region" aria-roledescription="carousel" aria-label="Featured travel destinations">
                <div class="sh-journey-viewport">
                    <div class="sh-journey-track">
                        @foreach ($journeySlides as $journeySlide)
                            <article class="sh-journey-slide" aria-roledescription="slide" aria-label="{{ $loop->iteration }} of {{ count($journeySlides) }}" @unless ($loop->first) aria-hidden="true" inert @endunless>
                                <img src="{{ $journeySlide['image'] }}" alt="{{ $journeySlide['alt'] }}" loading="eager" decoding="async">
                                @if ($journeySlide['video'] !== '')
                                    <video class="sh-journey-video" src="{{ $journeySlide['video'] }}" poster="{{ $journeySlide['image'] }}" muted loop playsinline preload="{{ $loop->first ? 'auto' : 'none' }}" @if ($loop->first) autoplay @endif aria-hidden="true" data-sh-journey-video></video>
                                @endif
                                <div class="sh-journey-caption"><span>{{ $journeySlide['eyebrow'] }}</span><h3>{{ $journeySlide['title'] }}</h3><p>{{ $journeySlide['body'] }}</p><a href="{{ $journeySlide['link_url'] }}">{{ $journeySlide['link_label'] }} <span aria-hidden="true">↗</span></a></div>
                            </article>
                        @endforeach
                    </div>
                    <div class="sh-journey-controls">
                        <div class="sh-journey-dots" role="group" aria-label="Choose a destination">
                            @foreach ($journeySlides as $journeySlide)
                                <button type="button" aria-label="Show destination {{ $loop->iteration }}: {{ $journeySlide['title'] }}" @if ($loop->first) aria-current="true" @endif data-sh-journey-select data-sh-journey-index="{{ $loop->index }}"></button>
                            @endforeach
                        </div>
                        <button type="button" data-sh-journey-previous aria-label="Previous destination" disabled>‹</button>
                        <button type="button" data-sh-journey-next aria-label="Next destination">›</button>
                    </div>
                </div>
                <div class="sh-journey-thumbnails" role="group" aria-label="Browse featured destinations">
                    @foreach ($journeySlides as $journeySlide)
                        <button type="button" data-sh-journey-select data-sh-journey-index="{{ $loop->index }}" aria-label="Show {{ $journeySlide['title'] }}" @if ($loop->first) aria-current="true" @endif>
                            <img src="{{ $journeySlide['image'] }}" alt="" loading="eager" decoding="async">@if ($journeySlide['video'] !== '')<em class="sh-journey-thumb-video">▶ Video</em>@endif<span>{{ $journeySlide['eyebrow'] }}</span><strong>{{ $journeySlide['thumb'] }}</strong>
                        </button>
                    @endforeach
                </div>
            </div>
        </section>

        @if ($popularTours !== [])
            @php($popularTourActiveIndex = min(2, count($popularTours) - 1))
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
                                                <img src="{{ $tour['image'] }}" alt="{{ $tour['title'] }}, {{ $tour['country'] }}" loading="eager" decoding="async">
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

        <section class="sh-cta" aria-labelledby="sh-cta-title">
            <h2 id="sh-cta-title">{{ $page->cta_heading }}</h2>
            <p>{{ $page->cta_body }}</p>
            <a class="sh-btn sh-btn-primary" href="{{ $page->whatsappUrl() }}" target="_blank" rel="noreferrer">{{ $page->cta_label }} <span aria-hidden="true">↗</span></a>
        </section>
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
