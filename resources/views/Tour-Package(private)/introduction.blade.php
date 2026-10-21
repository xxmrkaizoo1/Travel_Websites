<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#ffffff">
    <meta name="description" content="Explore private tours by Smart Muslim, with thoughtfully planned journeys across Southeast Asia.">
    <title>Private Tour Packages | Smart Muslim by Roystar Travel &amp; Tours</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="island-page">
    <a class="skip-link" href="#main">Skip to content</a>

    @include('partials.site-header')

    <main id="main">
        <section class="island-hero" aria-labelledby="private-tour-title">
            <img class="island-hero-image" src="https://images.unsplash.com/photo-1528181304800-259b08848526?auto=format&fit=crop&w=2000&q=90" alt="" aria-hidden="true">
            <div class="island-hero-shade"></div>
            <div class="island-hero-content">
                <span class="island-eyebrow">SMART MUSLIM · PRIVATE TOURS</span>
                <h1 id="private-tour-title">Travel your way.<br><strong>Make it meaningful.</strong></h1>
                <p>Discover thoughtfully planned private journeys, with the flexibility to explore at your own pace and enjoy experiences that matter to you.</p>
                <a class="island-cta" href="#destinations">Explore destinations <span aria-hidden="true">↓</span></a>
            </div>
            <span class="island-hero-note">PRIVATE JOURNEYS · THOUGHTFUL TRAVEL</span>
        </section>

        <section class="island-intro" aria-labelledby="private-tour-intro-title">
            <div class="island-intro-copy">
                <span class="island-section-label">TOUR PACKAGE (PRIVATE)</span>
                <h2 id="private-tour-intro-title">A journey planned around <span>you.</span></h2>
                <p>Choose the destinations that inspire you, travel with your own private itinerary and let our team help take care of the details. From island escapes to cultural discoveries, find a trip that fits your pace and interests.</p>
                <a class="island-text-link" href="#destinations">Choose your destination <span aria-hidden="true">→</span></a>
            </div>
            <div class="island-photo-stack">
                <img src="https://images.unsplash.com/photo-1537996194471-e657df975ab4?auto=format&fit=crop&w=1000&q=85" alt="Tropical island scenery in Southeast Asia" loading="lazy" decoding="async">
                <span class="island-photo-caption">Thoughtful journeys, made personal.</span>
            </div>
        </section>

        <section class="island-highlights" id="destinations" aria-labelledby="private-tour-destinations-title">
            <div class="island-section-heading">
                <span class="island-section-label">FIND YOUR NEXT JOURNEY</span>
                <h2 id="private-tour-destinations-title">Explore our destinations.</h2>
                <p>Start with a place that speaks to you. Each destination offers its own experiences, with private tour options to help you make the most of your stay.</p>
            </div>
            <div class="island-highlight-grid">
                <article class="island-highlight">
                    <span class="island-highlight-number">01</span>
                    <h3>Island &amp; Honeymoon</h3>
                    <p>Find a slower pace with romantic stays, island scenery and time to unwind.</p>
                    <a class="island-text-link" href="{{ route('island-honeymoon') }}">Explore island escapes <span aria-hidden="true">→</span></a>
                </article>
                <article class="island-highlight">
                    <span class="island-highlight-number">02</span>
                    <h3>Indonesia</h3>
                    <p>Discover Indonesia’s culture, coastlines, highlands and island landscapes.</p>
                    <a class="island-text-link" href="{{ route('indonesia') }}">Explore Indonesia <span aria-hidden="true">→</span></a>
                </article>
                <article class="island-highlight">
                    <span class="island-highlight-number">03</span>
                    <h3>Thailand</h3>
                    <p>Explore lively cities, northern scenery, local markets and tropical shores.</p>
                    <a class="island-text-link" href="{{ route('thailand') }}">Explore Thailand <span aria-hidden="true">→</span></a>
                </article>
                <article class="island-highlight">
                    <span class="island-highlight-number">04</span>
                    <h3>Vietnam</h3>
                    <p>Experience a rich blend of city life, heritage, scenery and local flavour.</p>
                    <a class="island-text-link" href="{{ route('vietnam') }}">Explore Vietnam <span aria-hidden="true">→</span></a>
                </article>
                <article class="island-highlight">
                    <span class="island-highlight-number">05</span>
                    <h3>Philippines</h3>
                    <p>Make time for island scenery, welcoming communities and coastal discoveries.</p>
                    <a class="island-text-link" href="{{ route('philippines') }}">Explore the Philippines <span aria-hidden="true">→</span></a>
                </article>
            </div>
        </section>

        <section class="island-contact" id="contact" aria-labelledby="private-tour-contact-title">
            <div>
                <span class="island-section-label">LET’S PLAN YOUR TRIP</span>
                <h2 id="private-tour-contact-title">Your next journey starts here.</h2>
                <p>Tell us where you would like to go and what you would love to experience. Our team will help you plan a private tour that feels right for you.</p>
            </div>
            <a class="island-cta" href="https://wa.me/60165500590?text=Hi%2C%20I%27d%20like%20to%20ask%20about%20private%20tour%20packages." target="_blank" rel="noreferrer">Talk to our team <span aria-hidden="true">↗</span></a>
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
                <a href="{{ route('island-honeymoon') }}">Island &amp; Honeymoon</a>
                <a href="{{ route('indonesia') }}">Indonesia</a>
                <a href="{{ route('thailand') }}">Thailand</a>
                <a href="#destinations">Private tour destinations</a>
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
