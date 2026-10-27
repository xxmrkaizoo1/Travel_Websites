<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#ffffff">
    <meta name="description" content="{{ $page->meta_description }}">
    <title>{{ $page->meta_title }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>@view-transition { navigation: auto; }</style>
</head>
<body class="gallery-page">
    <a class="skip-link" href="#main">Skip to content</a>

    @include('partials.site-header')

    <main id="main">
        <header class="smart-muslim-gallery-hero" style="background-image: url('{{ $page->imageUrl($page->hero_image) }}')">
            <span>{{ $page->hero_eyebrow }}</span>
            <h1 id="gallery-title">{{ $page->hero_title }}</h1>
            <p>{{ $page->hero_body }}</p>
        </header>

        <section class="smart-muslim-gallery smart-muslim-gallery-page" aria-labelledby="gallery-title">
            <div class="smart-muslim-gallery-inner">
                <div class="gallery-marquee" aria-label="Destination gallery">
                    <div class="gallery-marquee-track">
                        @foreach ([false, true] as $isCopy)
                            @foreach ($page->destinations as $destination)
                                <figure class="smart-muslim-gallery-card" @if ($isCopy) aria-hidden="true" @endif>
                                    <img src="{{ $page->imageUrl($destination['flag']) }}" alt="{{ $isCopy ? '' : 'Flag of '.$destination['country'] }}" loading="lazy">
                                    <figcaption>{{ $destination['country'] }}</figcaption>
                                </figure>
                            @endforeach
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        <section class="travel-moments" aria-labelledby="travel-moments-title">
            <div class="travel-moments__inner">
                <header class="travel-moments__heading">
                    <div>
                        <span class="travel-moments__eyebrow">{{ $page->moments_eyebrow }}</span>
                        <h2 id="travel-moments-title">{{ $page->moments_heading }}</h2>
                    </div>
                    <p>{{ $page->moments_body }}</p>
                </header>

                <div class="travel-moments__grid">
                    @foreach ($page->destinations as $destination)
                        <article class="travel-moment-card">
                            <div class="travel-moment-card__image">
                                <img src="{{ $page->imageUrl($destination['photo']) }}" alt="{{ $destination['photo_alt'] }}" loading="lazy">
                                <span class="travel-moment-card__number" aria-hidden="true">{{ sprintf('%02d', $loop->iteration) }}</span>
                                <span class="travel-moment-card__flag" aria-hidden="true">
                                    <img src="{{ $page->imageUrl($destination['flag']) }}" alt="">
                                </span>
                                <span class="travel-moment-card__label">{{ $page->moments_card_label }}</span>
                            </div>
                            <div class="travel-moment-card__caption">
                                <div>
                                    <span class="travel-moment-card__eyebrow">{{ $page->moments_card_eyebrow }}</span>
                                    <h3>{{ $destination['country'] }}</h3>
                                </div>
                                <span aria-hidden="true">↗</span>
                            </div>
                            <button class="travel-moment-card__open" type="button" data-gallery-album-open="{{ $loop->index }}" aria-haspopup="dialog" aria-controls="gallery-album">
                                <span class="visually-hidden">View {{ $destination['country'] }} photo gallery</span>
                            </button>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        @include('partials.gallery-album', ['eyebrow' => $page->moments_eyebrow, 'footerNote' => $page->moments_card_label])
        @php
            $albums = array_map(fn (array $destination): array => [
                'country' => $destination['country'],
                'flag' => $page->imageUrl($destination['flag']),
                'photos' => $page->albumPhotos($destination),
            ], $page->destinations);
        @endphp
        <script type="application/json" id="gallery-album-data">@json($albums)</script>
    </main>

    <footer class="site-footer">
        <div class="footer-inner">
            <a class="footer-logo" href="{{ route('smart-muslim') }}" aria-label="Smart Muslim home">
                <img src="{{ asset('images/smart-muslim-logo.png') }}" alt="Smart Muslim">
            </a>
            <div class="footer-company">
                <h2>ROYSTAR TRAVEL &amp; TOURS SDN BHD</h2>
                <p>No.16A &amp; 16B, Lorong Brunei 3,<br>Off Jalan Pudu, 55100 Kuala Lumpur,<br>WPKL, Malaysia.</p>
                <a href="tel:+60321430188">Tour: +603 2143 0188 / 0288</a>
                <a href="mailto:smart_muslim@roystartravel.com.my">smart_muslim@roystartravel.com.my</a>
            </div>
            <div class="footer-links">
                <h2>Quick Links</h2>
                <a href="{{ route('smart-muslim') }}">Smart Muslim Home</a>
                <a href="{{ route('gallery') }}">Our Gallery</a>
                <a href="{{ route('china-muslim') }}">China Muslim</a>
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
