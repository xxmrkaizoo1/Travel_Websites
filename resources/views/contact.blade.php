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
<body class="contact-page">
    <a class="skip-link" href="#main">Skip to content</a>

    @include('partials.site-header')

    <main id="main">
        <section class="ct-hero" aria-labelledby="contact-title">
            <img class="ct-hero__image" src="{{ $page->heroImageUrl() }}" alt="" aria-hidden="true" fetchpriority="high">
            <div class="ct-hero__inner">
                <span class="ct-hero__eyebrow">{{ $page->hero_eyebrow }}</span>
                <h1 id="contact-title">{{ $page->hero_title }}</h1>
                <p>{{ $page->hero_body }}</p>
            </div>
        </section>

        <section class="ct-main" aria-labelledby="contact-companies-title">
            <div class="ct-main__inner">
                <div class="ct-companies">
                    <h2 id="contact-companies-title">{{ $page->companies_heading }}</h2>
                    @foreach ($page->companies as $company)
                        <article class="ct-company">
                            <div class="ct-company__logo">
                                @if (filled($company['logo']))
                                    <img src="{{ $page->imageUrl($company['logo']) }}" alt="{{ $company['name'] }} logo" loading="lazy">
                                @else
                                    <span aria-hidden="true">{{ mb_substr($company['name'], 0, 1) }}</span>
                                @endif
                            </div>
                            <div class="ct-company__body">
                                <h3>{{ $company['name'] }}</h3>
                                <ul class="ct-company__lines">
                                    <li>
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 22s7-6.2 7-12a7 7 0 1 0-14 0c0 5.8 7 12 7 12Zm0-9.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z"/></svg>
                                        <span class="visually-hidden">Address:</span>
                                        <address>{{ $company['address'] }}</address>
                                    </li>
                                    @if (filled($company['phone']))
                                        <li>
                                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6.6 10.8a15.1 15.1 0 0 0 6.6 6.6l2.2-2.2a1 1 0 0 1 1-.25 11.4 11.4 0 0 0 3.6.57 1 1 0 0 1 1 1V20a1 1 0 0 1-1 1A17 17 0 0 1 3 4a1 1 0 0 1 1-1h3.5a1 1 0 0 1 1 1c0 1.25.2 2.45.57 3.57a1 1 0 0 1-.25 1L6.6 10.8Z"/></svg>
                                            <span class="visually-hidden">Phone:</span>
                                            <span>
                                                @foreach ($page->numberLinks($company['phone']) as $number)
                                                    <a href="{{ $number['href'] }}">{{ $number['label'] }}</a>@unless ($loop->last)<span aria-hidden="true"> / </span>@endunless
                                                @endforeach
                                            </span>
                                        </li>
                                    @endif
                                    @if (filled($company['whatsapp']))
                                        <li>
                                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2Zm5.3 14.1c-.2.6-1.3 1.2-1.8 1.2-.5.1-1 .2-3.3-.7a11.7 11.7 0 0 1-4.6-4.1c-.3-.5-1.1-1.5-1.1-2.9s.7-2 1-2.3c.2-.3.5-.3.7-.3h.5c.2 0 .4 0 .6.5l.8 2c.1.2.1.4 0 .5l-.3.5-.4.4c-.1.2-.3.3-.1.6.2.3.8 1.3 1.6 2 1.1 1 2 1.3 2.3 1.4.3.2.5.1.6-.1l.9-1c.2-.3.4-.2.6-.1l1.9.9c.3.1.5.2.5.3.1.2.1.7-.1 1.2Z"/></svg>
                                            <span>
                                                WhatsApp:
                                                @foreach ($page->numberLinks($company['whatsapp'], whatsapp: true) as $number)
                                                    <a href="{{ $number['href'] }}" target="_blank" rel="noreferrer">{{ $number['label'] }}</a>@unless ($loop->last)<span aria-hidden="true"> / </span>@endunless
                                                @endforeach
                                            </span>
                                        </li>
                                    @endif
                                    @if (filled($company['email']))
                                        <li>
                                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 5h18a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Zm9 7.2L4 7v1.9l8 5.1 8-5.1V7l-8 5.2Z"/></svg>
                                            <span class="visually-hidden">Email:</span>
                                            <a href="mailto:{{ $company['email'] }}">{{ $company['email'] }}</a>
                                        </li>
                                    @endif
                                    @if (filled($company['website']))
                                        <li>
                                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20Zm6.9 6h-2.9a15.6 15.6 0 0 0-1.4-3.6A8 8 0 0 1 18.9 8ZM12 4c.8 1.2 1.5 2.5 1.9 4h-3.8c.4-1.5 1.1-2.8 1.9-4ZM4.3 14a8 8 0 0 1 0-4h3.4a16.5 16.5 0 0 0 0 4H4.3Zm.8 2h2.9c.3 1.3.8 2.5 1.4 3.6A8 8 0 0 1 5.1 16ZM8 8H5.1a8 8 0 0 1 4.3-3.6C8.8 5.5 8.3 6.7 8 8Zm4 12c-.8-1.2-1.5-2.5-1.9-4h3.8c-.4 1.5-1.1 2.8-1.9 4Zm2.3-6H9.7a14.7 14.7 0 0 1 0-4h4.6a14.7 14.7 0 0 1 0 4Zm.3 5.6c.6-1.1 1.1-2.3 1.4-3.6h2.9a8 8 0 0 1-4.3 3.6Zm1.7-5.6a16.5 16.5 0 0 0 0-4h3.4a8 8 0 0 1 0 4h-3.4Z"/></svg>
                                            <span class="visually-hidden">Website:</span>
                                            <a href="{{ $page->websiteUrl($company['website']) }}" target="_blank" rel="noreferrer">{{ $company['website'] }}</a>
                                        </li>
                                    @endif
                                </ul>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="ct-map" aria-label="Our location on the map">
            <div class="ct-map__frame">
                <iframe src="{{ $page->mapEmbedUrl() }}" title="Map: {{ $page->map_query }}" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
                <a class="ct-map__directions" href="{{ $page->mapDirectionsUrl() }}" target="_blank" rel="noreferrer">Get directions <span aria-hidden="true">↗</span></a>
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
