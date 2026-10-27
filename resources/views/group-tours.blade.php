<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#ffffff">
    <meta name="description" content="{{ $page->meta_description }}">
    <title>{{ $page->meta_title }}</title>
    <style>@view-transition { navigation: auto; }</style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
@php($isGround = $page->kind === \App\Models\GroupToursPage::KIND_GROUND)
<body class="group-tours-page{{ $isGround ? ' ground-tours-page' : '' }}{{ $page->kind === \App\Models\GroupToursPage::KIND_DOMESTIC ? ' domestic-tours-page' : '' }}">
    <a class="skip-link" href="#main">Skip to content</a>

    @include('partials.site-header')

    <main id="main">
        <header class="focus-tours-hero{{ filled($page->hero_video) ? ' has-video' : '' }}" style="--focus-hero-image: url('{{ $page->imageUrl($page->hero_image) }}')">
            @if (filled($page->hero_video))
                <video class="focus-tours-hero__video" src="{{ $page->imageUrl($page->hero_video) }}" poster="{{ $page->imageUrl($page->hero_image) }}" autoplay muted loop playsinline aria-hidden="true" data-background-video></video>
            @endif
            <div class="focus-tours-hero__inner">
                <span class="focus-tours-hero__eyebrow">{{ $page->hero_eyebrow }}</span>
                <h1 id="focus-tours-title">{{ $page->heading }}</h1>
                @if (filled($page->intro))
                    <p>{{ $page->intro }}</p>
                @endif
                <div class="focus-tours-hero__meta">
                    <span><strong>{{ count($page->tours) }}</strong> {{ \Illuminate\Support\Str::plural('destination', count($page->tours)) }}</span>
                    <a href="#focus-tours-grid">Browse tours <span aria-hidden="true">↓</span></a>
                </div>
            </div>
            @if ($isGround)
                <aside class="ground-hero-panel" aria-label="Arranged on the ground">
                    <p class="ground-hero-panel__title">Arranged on the ground</p>
                    <ul>
                        <li><span aria-hidden="true">🏨</span><div><strong>Hotels</strong><small>Handpicked stays near the sights</small></div></li>
                        <li><span aria-hidden="true">🚐</span><div><strong>Transport</strong><small>Airport transfers and private vehicles</small></div></li>
                        <li><span aria-hidden="true">🧭</span><div><strong>Local guides</strong><small>Day tours with people who know the area</small></div></li>
                        <li><span aria-hidden="true">✈️</span><div><strong>Your flights</strong><small>Book them yourself, at the fare you like</small></div></li>
                    </ul>
                </aside>
            @endif
        </header>

        <section class="focus-tours" id="focus-tours-grid" aria-labelledby="focus-tours-title">
            <div class="focus-tours__inner">
                @php($tourCategories = $page->tourCategories())
                <div class="focus-tours__toolbar" data-tour-finder>
                    <label class="focus-tours__search">
                        <span class="visually-hidden">Search destinations</span>
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="2"/><path d="m20 20-3.5-3.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                        <input type="search" placeholder="Search destinations…" autocomplete="off" data-tour-search>
                    </label>
                    @if (count($tourCategories) > 1)
                        <div class="focus-tours__filters" role="group" aria-label="Filter destinations">
                            <button type="button" aria-pressed="true" data-tour-filter="">All</button>
                            @foreach ($tourCategories as $category)
                                <button type="button" aria-pressed="false" data-tour-filter="{{ $category }}">{{ $category }}</button>
                            @endforeach
                        </div>
                    @endif
                    <p class="focus-tours__count" aria-live="polite" data-tour-count>{{ count($page->tours) }} {{ \Illuminate\Support\Str::plural('destination', count($page->tours)) }}</p>
                </div>

                <div class="focus-tours__slider" data-card-slider>
                    <button class="focus-tours__arrow focus-tours__arrow--prev" type="button" aria-label="Previous destinations" data-slider-prev hidden><span aria-hidden="true">‹</span></button>
                    <div class="focus-tours__track" data-slider-track>
                        @foreach ($page->tours as $tour)
                            @php($hasPage = filled($tour['link_url'] ?? ''))
                            <a class="focus-tour-card" id="{{ $page->tourAnchor($tour['name']) }}" data-tour-card data-category="{{ $tour['category'] ?? '' }}" data-search="{{ \Illuminate\Support\Str::lower(implode(' ', [$tour['name'], $tour['tagline'] ?? '', $tour['category'] ?? ''])) }}" @if ($hasPage) href="{{ $tour['link_url'] }}" @else href="{{ $page->tourEnquiryUrl($tour['name']) }}" target="_blank" rel="noreferrer" @endif>
                                <img src="{{ $page->imageUrl($tour['image']) }}" alt="{{ $tour['alt'] }}" loading="{{ $loop->index < 4 ? 'eager' : 'lazy' }}">
                                <span class="focus-tour-card__number" aria-hidden="true">{{ sprintf('%02d', $loop->iteration) }}</span>
                                @if (filled($tour['category'] ?? ''))
                                    <span class="focus-tour-card__tag">{{ $tour['category'] }}</span>
                                @endif
                                <span class="focus-tour-card__body">
                                    <span class="focus-tour-card__label">{{ $tour['name'] }}</span>
                                    @if (filled($tour['tagline'] ?? ''))
                                        <span class="focus-tour-card__tagline">{{ $tour['tagline'] }}</span>
                                    @endif
                                    @if ($isGround)
                                        <span class="ground-card__includes" aria-label="Arranged for you"><span>🏨 Hotels</span><span>🚐 Transport</span><span>🧭 Guides</span></span>
                                    @endif
                                    <span class="focus-tour-card__action">
                                        @if ($hasPage)
                                            Explore tours <span aria-hidden="true">→</span>
                                        @else
                                            Enquire on WhatsApp <span aria-hidden="true">↗</span>
                                        @endif
                                    </span>
                                </span>
                            </a>
                        @endforeach
                    </div>
                    <button class="focus-tours__arrow focus-tours__arrow--next" type="button" aria-label="Next destinations" data-slider-next hidden><span aria-hidden="true">›</span></button>
                </div>
                <div class="focus-tours__dots" data-slider-dots hidden></div>

                <p class="focus-tours__empty" data-tour-empty hidden>
                    No destinations match your search. <button type="button" data-tour-reset>Show all destinations</button>
                </p>
            </div>
        </section>

        @if ($isGround)
            <section class="ground-steps" aria-labelledby="ground-steps-title">
                <div class="ground-steps__inner">
                    <p class="ground-steps__kicker">How it works</p>
                    <h2 id="ground-steps-title">Three steps to your ground tour</h2>
                    <ol>
                        <li><span>1</span><strong>Pick a destination</strong><p>Choose a country above and tell us your dates and group size.</p></li>
                        <li><span>2</span><strong>We plan the ground</strong><p>Our team arranges hotels, transport, guides and day tours.</p></li>
                        <li><span>3</span><strong>Fly in and enjoy</strong><p>Book your own flights; we meet you on arrival and handle the rest.</p></li>
                    </ol>
                </div>
            </section>
        @endif

        <section class="focus-tours-cta" aria-labelledby="focus-tours-cta-title">
            <div class="focus-tours-cta__inner">
                <div>
                    <h2 id="focus-tours-cta-title">{{ $page->cta_heading }}</h2>
                    <p>{{ $page->cta_body }}</p>
                </div>
                <a class="focus-tours-cta__button" href="{{ $page->whatsappUrl() }}" target="_blank" rel="noreferrer">{{ $page->cta_label }} <span aria-hidden="true">↗</span></a>
            </div>
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
                <a href="{{ route('home') }}">Smart Holidays Home</a>
                <a href="{{ route('group-tours') }}">Group Tours</a>
                <a href="{{ route('ground-tours') }}">Ground Tours</a>
                <a href="{{ route('domestic-tours') }}">Domestic Tours</a>
                <a href="{{ route('group-tours.booking-conditions') }}">Booking Conditions</a>
                <a href="{{ route('smart-muslim') }}">Smart Muslim Tour</a>
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
