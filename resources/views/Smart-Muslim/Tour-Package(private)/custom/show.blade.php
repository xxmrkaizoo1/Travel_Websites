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
<body class="island-page indonesia-page {{ $page->hasItineraries() ? 'bali-tour-page' : '' }} {{ $page->themeClass() }}">
    <a class="skip-link" href="#main">Skip to content</a>

    @include('partials.site-header')

    @if ($page->slug === 'series-departures-2026' && $seriesPages->isNotEmpty())
        @php($seriesPageSlides = collect([$page])->concat($seriesPages)->values())
        <div class="series-page-slider" id="main" data-series-page-slider tabindex="-1">
            <div class="series-page-panels">
                @foreach ($seriesPageSlides as $seriesPageSlide)
                    <section class="series-page-panel" id="series-page-panel-{{ $seriesPageSlide->slug }}" data-series-page-slide="{{ $seriesPageSlide->slug }}" aria-labelledby="{{ $seriesPageSlide->slug }}-title" aria-hidden="{{ $loop->first ? 'false' : 'true' }}" @unless ($loop->first) hidden inert @endunless>
                        @include($seriesPageSlide->hasItineraries() ? 'partials.tour-package-guide-main' : 'partials.tour-package-main', ['page' => $seriesPageSlide, 'seriesPageMainId' => 'series-content-'.$seriesPageSlide->slug])
                    </section>
                @endforeach
            </div>
            <div class="series-page-controls">
                <button type="button" data-series-page-previous aria-label="Previous page" disabled>←</button>
                <span data-series-page-count aria-live="polite">Page 1 of {{ count($seriesPageSlides) }}</span>
                <button type="button" data-series-page-next aria-label="Next page">→</button>
            </div>
        </div>
    @else
        @include($page->hasItineraries() ? 'partials.tour-package-guide-main' : 'partials.tour-package-main', ['page' => $page])
    @endif

    <footer class="site-footer">
        <div class="footer-inner">
            <a class="footer-logo" href="{{ route('smart-muslim') }}" aria-label="Smart Muslim home">
                <img src="{{ asset('images/smart-muslim-logo.png') }}" alt="Smart Muslim">
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
