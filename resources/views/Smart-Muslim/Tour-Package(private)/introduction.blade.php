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
<body class="island-page private-tours-page">
    <a class="skip-link" href="#main">Skip to content</a>

    @include('partials.site-header')

    <main id="main">
        <section class="pt-hero" aria-labelledby="private-tour-title">
            <img class="pt-hero__image" src="{{ $page->heroImageUrl() }}" alt="" aria-hidden="true" fetchpriority="high">
            <div class="pt-hero__shade" aria-hidden="true"></div>
            <div class="pt-hero__inner">
                <div class="pt-hero__content">
                    <span class="pt-hero__eyebrow"><span aria-hidden="true">☪</span> {{ $page->hero_eyebrow }}</span>
                    <h1 id="private-tour-title">{{ $page->hero_title }} <strong>{{ $page->hero_highlight }}</strong></h1>
                    <p>{{ $page->hero_body }}</p>
                    <div class="pt-hero__actions">
                        <a class="pt-button pt-button--light" href="#destinations">{{ $page->hero_cta_label }} <span aria-hidden="true">↓</span></a>
                        <a class="pt-button pt-button--ghost" href="{{ $page->contactUrl() }}" target="_blank" rel="noreferrer">{{ $page->contact_cta_label }} <span aria-hidden="true">↗</span></a>
                    </div>
                </div>
                <a class="pt-hero__card" href="#destinations">
                    <span class="pt-hero__thumbs" aria-hidden="true">
                        @foreach (array_slice($page->destinations, 0, 4) as $destination)
                            <img src="{{ $page->imageUrl($destination['image']) }}" alt="">
                        @endforeach
                    </span>
                    <strong>{{ count($page->destinations) }} {{ \Illuminate\Support\Str::plural('destination', count($page->destinations)) }}</strong>
                    <small>Private itineraries, planned around you</small>
                </a>
            </div>
            @if (filled($page->hero_note))
                <span class="pt-hero__note">{{ $page->hero_note }}</span>
            @endif
        </section>

        @if ($page->perks !== [])
            <section class="pt-perks" aria-label="Why travel privately with Smart Muslim">
                <ul>
                    @foreach ($page->perks as $perk)
                        <li>
                            @if (filled($perk['icon']))
                                <span class="pt-perks__icon" aria-hidden="true">{{ $perk['icon'] }}</span>
                            @endif
                            <div>
                                <strong>{{ $perk['title'] }}</strong>
                                @if (filled($perk['body']))
                                    <small>{{ $perk['body'] }}</small>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        <section class="pt-intro" aria-labelledby="private-tour-intro-title">
            <div class="pt-intro__copy">
                <span class="pt-label">{{ $page->intro_label }}</span>
                <h2 id="private-tour-intro-title">{{ $page->intro_heading }} @if (filled($page->intro_highlight))<span>{{ $page->intro_highlight }}</span>@endif</h2>
                <p>{{ $page->intro_body }}</p>
                <a class="pt-text-link" href="#destinations">{{ $page->intro_link_label }} <span aria-hidden="true">→</span></a>
            </div>
            <figure class="pt-intro__media">
                <img src="{{ $page->introImageUrl() }}" alt="{{ $page->intro_image_alt }}" loading="lazy" decoding="async">
                @if (filled($page->intro_caption))
                    <figcaption><span aria-hidden="true">✦</span> {{ $page->intro_caption }}</figcaption>
                @endif
            </figure>
        </section>

        <section class="pt-destinations" id="destinations" aria-labelledby="private-tour-destinations-title">
            <div class="pt-section-heading">
                <span class="pt-label">{{ $page->destinations_label }}</span>
                <h2 id="private-tour-destinations-title">{{ $page->destinations_heading }}</h2>
                @if (filled($page->destinations_body))
                    <p>{{ $page->destinations_body }}</p>
                @endif
            </div>
            <div class="pt-destination-grid">
                @foreach ($page->destinations as $destination)
                    <a class="pt-destination" href="{{ $destination['link_url'] }}">
                        <img src="{{ $page->imageUrl($destination['image']) }}" alt="{{ $destination['alt'] }}" loading="lazy" decoding="async">
                        <span class="pt-destination__number" aria-hidden="true">{{ sprintf('%02d', $loop->iteration) }}</span>
                        <span class="pt-destination__body">
                            <strong>{{ $destination['name'] }}</strong>
                            @if (filled($destination['tagline']))
                                <small>{{ $destination['tagline'] }}</small>
                            @endif
                            <span class="pt-destination__link">{{ filled($destination['link_label']) ? $destination['link_label'] : 'Explore' }} <span aria-hidden="true">→</span></span>
                        </span>
                    </a>
                @endforeach
            </div>
        </section>

        <section class="pt-contact" id="contact" aria-labelledby="private-tour-contact-title">
            <div class="pt-contact__inner">
                <div>
                    <span class="pt-label pt-label--light">{{ $page->contact_label }}</span>
                    <h2 id="private-tour-contact-title">{{ $page->contact_heading }}</h2>
                    <p>{{ $page->contact_body }}</p>
                </div>
                <a class="pt-button pt-button--light" href="{{ $page->contactUrl() }}" target="_blank" rel="noreferrer">{{ $page->contact_cta_label }} <span aria-hidden="true">↗</span></a>
            </div>
        </section>
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
