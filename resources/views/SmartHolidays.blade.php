<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#ffffff">
    <meta name="description" content="Smart Holidays by Roystar Travel & Tours. Explore group tours, ground tours, domestic trips and Smart Muslim tours.">
    <title>Smart Holidays by Roystar Travel &amp; Tours</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="smart-home">
    <a class="skip-link" href="#main">Skip to content</a>

    @include('partials.site-header')

    <a class="admin-portal-fab" href="{{ route('login') }}" aria-label="Open admin login" title="Admin login">
        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M4 20V7.5L12 4l8 3.5V20H4Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
            <path d="M9 20v-5a3 3 0 0 1 6 0v5M8 9h.01M12 9h.01M16 9h.01" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
        </svg>
        <span>Admin</span>
    </a>

    <main id="main">
        <section class="sh-hero" id="explore" aria-labelledby="smart-intro-title">
            <img class="sh-hero-image" src="{{ asset('images/smart-holidays-banner.jpg') }}" alt="Smart Holidays travel banner featuring Big Ben, Swiss mountain train, Cappadocia balloons, Taiwan railway, Mount Fuji and Shanghai skyline" width="1920" height="600" fetchpriority="high">
            <div class="sh-hero-overlay" aria-hidden="true"></div>
            <div class="sh-hero-content">
                <span class="sh-eyebrow">WELCOME TO SMART HOLIDAYS</span>
                <h1 id="smart-intro-title">Your world of journeys, thoughtfully planned.</h1>
                <p>From group tours and ground arrangements to domestic getaways and Muslim-friendly holidays, Smart Holidays by Roystar Travel &amp; Tours helps you explore the world your way.</p>
                <div class="sh-hero-actions">
                    <a class="sh-btn sh-btn-primary" href="https://wa.me/60165500590" target="_blank" rel="noreferrer">Plan my trip <span aria-hidden="true">↗</span></a>
                    <a class="sh-btn sh-btn-ghost" href="#brands">Explore tours <span aria-hidden="true">↓</span></a>
                </div>
            </div>
            <ul class="sh-stats" aria-label="Why travellers choose us">
                <li><strong>Group</strong><span>Escorted tours</span></li>
                <li><strong>Ground</strong><span>Tailored arrangements</span></li>
                <li><strong>Domestic</strong><span>Local getaways</span></li>
                <li><strong>Muslim</strong><span>Friendly itineraries</span></li>
            </ul>
        </section>

        <section class="sh-services" aria-labelledby="sh-services-title">
            <div class="island-section-heading">
                <span class="island-section-label">WHAT WE DO</span>
                <h2 id="sh-services-title">Everything for your next escape.</h2>
            </div>
            <div class="sh-service-grid">
                <article class="sh-service">
                    <span class="sh-service-icon" aria-hidden="true">🌍</span>
                    <h3>Group Tours</h3>
                    <p>Join curated escorted journeys across Asia, Europe and beyond.</p>
                </article>
                <article class="sh-service">
                    <span class="sh-service-icon" aria-hidden="true">🧭</span>
                    <h3>Ground Tours</h3>
                    <p>Transfers, guides and stays arranged seamlessly at your destination.</p>
                </article>
                <article class="sh-service">
                    <span class="sh-service-icon" aria-hidden="true">🏝️</span>
                    <h3>Domestic Tours</h3>
                    <p>Discover Malaysia with easy, well-paced local getaways.</p>
                </article>
                <article class="sh-service">
                    <span class="sh-service-icon" aria-hidden="true">✈️</span>
                    <h3>Ticketing</h3>
                    <p>Flights and travel tickets handled by our friendly team.</p>
                </article>
            </div>
        </section>

        <section class="smart-brands" id="brands" aria-labelledby="smart-brands-title">
            <div class="island-section-heading">
                <span class="island-section-label">OUR BRANDS</span>
                <h2 id="smart-brands-title">Choose how you want to travel.</h2>
            </div>
            <div class="smart-brand-grid">
                <article class="smart-brand-card">
                    <img src="{{ asset('images/smart-holidays-logo.png') }}" alt="Smart Holidays logo" loading="lazy">
                    <h3>Smart Holidays</h3>
                    <p>Group tours, ground tours and domestic travel crafted for every kind of traveller.</p>
                    <a class="island-cta" href="https://wa.me/60165500590?text=Hi%2C%20I%27d%20like%20to%20ask%20about%20Smart%20Holidays%20tours." target="_blank" rel="noreferrer">Enquire on WhatsApp <span aria-hidden="true">↗</span></a>
                </article>
                <article class="smart-brand-card smart-brand-card-muslim">
                    <span class="smart-brand-badge"><strong>SMART</strong><span>MUSLIM</span></span>
                    <h3>Smart Muslim Tour</h3>
                    <p>Muslim-friendly private packages, series tours and island &amp; honeymoon getaways.</p>
                    <a class="island-cta" href="{{ route('smart-muslim') }}">Explore Smart Muslim <span aria-hidden="true">→</span></a>
                </article>
            </div>
        </section>

        <section class="sh-cta" aria-labelledby="sh-cta-title">
            <h2 id="sh-cta-title">Ready for your next journey?</h2>
            <p>Talk to our travel team and get a plan made for you.</p>
            <a class="sh-btn sh-btn-primary" href="https://wa.me/60165500590" target="_blank" rel="noreferrer">WhatsApp Us <span aria-hidden="true">↗</span></a>
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
                <a href="https://smartholidays.com.my/privacy-policy/" target="_blank" rel="noreferrer">Privacy Policy</a>
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
