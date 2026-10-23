<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#ffffff">
    <meta name="description" content="Explore the destinations of Smart Muslim by Roystar Travel &amp; Tours.">
    <title>Our Gallery | Smart Muslim by Roystar Travel &amp; Tours</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="gallery-page">
    <a class="skip-link" href="#main">Skip to content</a>

    @include('partials.site-header')

    <main id="main">
        <header class="smart-muslim-gallery-hero" style="background-image: url('{{ asset('images/series/danang-1.jpg') }}')">
            <span>OUR JOURNEYS</span>
            <h1 id="gallery-title">Smart Muslim Gallery</h1>
            <p>A glimpse of the places and experiences waiting for you.</p>
        </header>

        <section class="smart-muslim-gallery smart-muslim-gallery-page" aria-labelledby="gallery-title">
            <div class="smart-muslim-gallery-inner">
                <div class="gallery-marquee" aria-label="Destination gallery">
                    <div class="gallery-marquee-track">
                        @foreach ([false, true] as $isCopy)
                            @foreach ($destinations as $destination)
                                <figure class="smart-muslim-gallery-card" @if ($isCopy) aria-hidden="true" @endif>
                                    <img src="{{ asset($destination['image']) }}" alt="{{ $isCopy ? '' : 'Flag of '.$destination['country'] }}" loading="lazy">
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
                        <span class="travel-moments__eyebrow">POSTCARDS FROM EVERYWHERE</span>
                        <h2 id="travel-moments-title">A closer look at the journey</h2>
                    </div>
                    <p>Take a little inspiration from the places, colors, and moments that make every trip unforgettable.</p>
                </header>

                <div class="travel-moments__grid">
                    @foreach ($destinations as $destination)
                        <article class="travel-moment-card">
                            <div class="travel-moment-card__image">
                                <img src="{{ asset($destination['gallery_image']) }}" alt="{{ $destination['gallery_alt'] }}" loading="lazy">
                                <span class="travel-moment-card__number" aria-hidden="true">{{ sprintf('%02d', $loop->iteration) }}</span>
                                <span class="travel-moment-card__flag" aria-hidden="true">
                                    <img src="{{ asset($destination['image']) }}" alt="">
                                </span>
                                <span class="travel-moment-card__label">A JOURNEY TO REMEMBER</span>
                            </div>
                            <div class="travel-moment-card__caption">
                                <div>
                                    <span class="travel-moment-card__eyebrow">EXPLORE</span>
                                    <h3>{{ $destination['country'] }}</h3>
                                </div>
                                <span aria-hidden="true">↗</span>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
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
