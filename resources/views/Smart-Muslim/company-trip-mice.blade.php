<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#ffffff">
    <meta name="description" content="Smart Muslim customises company trips and MICE programmes for government and private sectors, with 30 years of experience.">
    <title>Company Trip (MICE) | Smart Muslim by Roystar Travel &amp; Tours</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .mice-clientele { padding: 5rem 1.5rem; text-align: center; background: linear-gradient(180deg, #f6f8fb 0%, #fff 100%); }
        .mice-clientele .mice-eyebrow { display: block; font-size: .78rem; letter-spacing: .22em; font-weight: 700; color: #3e5dd2; margin-bottom: .75rem; }
        .mice-clientele h2 { font-size: clamp(1.8rem, 3.4vw, 2.6rem); font-weight: 700; margin: 0 0 .75rem; color: #14213d; }
        .mice-clientele .mice-sub { max-width: 560px; margin: 0 auto 2.75rem; color: #5b6475; line-height: 1.6; }
        .mice-slider { overflow: hidden; max-width: 1280px; margin: 0 auto; padding: 1rem 0 1.5rem; mask-image: linear-gradient(90deg, transparent, #000 8%, #000 92%, transparent); }
        .mice-track { display: flex; gap: 1.25rem; width: max-content; animation: mice-scroll 45s linear infinite; }
        .mice-slider:hover .mice-track { animation-play-state: paused; }
        .mice-slide { flex: 0 0 auto; width: 210px; height: 130px; display: flex; align-items: center; justify-content: center; padding: 1.25rem; background: #fff; border: 1px solid #e6eaf2; border-radius: 16px; box-shadow: 0 6px 18px rgba(20, 33, 61, .06); transition: transform .3s ease, box-shadow .3s ease; }
        .mice-slide:hover { transform: translateY(-6px); box-shadow: 0 14px 28px rgba(62, 93, 210, .18); }
        .mice-slide img { width: 100%; height: 100%; object-fit: contain; filter: grayscale(1); opacity: .75; transition: filter .3s ease, opacity .3s ease; }
        .mice-slide:hover img { filter: none; opacity: 1; }
        @keyframes mice-scroll { to { transform: translateX(calc(-50% - .625rem)); } }
        .mice-gallery { position: relative; padding: 5.5rem 0 4.5rem; text-align: center; background: radial-gradient(circle at 15% 0%, rgba(62, 93, 210, .10), transparent 45%), radial-gradient(circle at 90% 100%, rgba(62, 93, 210, .08), transparent 40%), #f6f8fb; overflow: hidden; }
        .mice-gallery .mice-eyebrow { display: inline-block; font-size: .78rem; letter-spacing: .22em; font-weight: 700; color: #3e5dd2; background: rgba(62, 93, 210, .1); padding: .4rem .9rem; border-radius: 999px; margin-bottom: 1rem; }
        .mice-gallery h2 { font-size: clamp(1.9rem, 3.6vw, 2.8rem); font-weight: 800; margin: 0 0 .75rem; color: #14213d; }
        .mice-gallery .mice-sub { max-width: 560px; margin: 0 auto 3rem; padding: 0 1.5rem; color: #5b6475; line-height: 1.6; }
        .mice-gallery-slider { overflow: hidden; padding: 1.5rem 0 2.5rem; mask-image: linear-gradient(90deg, transparent, #000 7%, #000 93%, transparent); }
        .mice-gallery-track { display: flex; align-items: center; gap: 1.5rem; width: max-content; animation: mice-gallery-scroll 70s linear infinite; }
        .mice-gallery-slider:hover .mice-gallery-track { animation-play-state: paused; }
        .mice-gallery-slide { position: relative; flex: 0 0 auto; width: 380px; height: 460px; margin: 0; overflow: hidden; border-radius: 26px; border: 6px solid #fff; background: #dfe6f5; box-shadow: 0 18px 40px rgba(20, 33, 61, .18); transition: transform .4s ease, box-shadow .4s ease; }
        .mice-gallery-slide:nth-child(even) { height: 380px; }
        .mice-gallery-slide:nth-child(3n) { transform: rotate(-1.2deg); }
        .mice-gallery-slide:nth-child(3n+1) { transform: rotate(1.2deg); }
        .mice-gallery-slide:hover { transform: translateY(-10px) rotate(0); box-shadow: 0 26px 50px rgba(62, 93, 210, .28); }
        .mice-gallery-slide img { width: 100%; height: 100%; object-fit: cover; transition: transform .7s ease; }
        .mice-gallery-slide:hover img { transform: scale(1.08); }
        .mice-gallery-slide figcaption { position: absolute; inset: auto 0 0; padding: 3.5rem 1.25rem 1.1rem; text-align: left; font-size: 1.05rem; font-weight: 700; color: #fff; background: linear-gradient(180deg, transparent, rgba(10, 20, 45, .82)); }
        @keyframes mice-gallery-scroll { to { transform: translateX(calc(-50% - .75rem)); } }
        @media (max-width: 640px) { .mice-gallery-slide { width: 260px; height: 340px; } .mice-gallery-slide:nth-child(even) { height: 290px; } }
        @media (prefers-reduced-motion: reduce) { .mice-gallery-track { animation: none; } .mice-gallery-slider { overflow-x: auto; } }
        @media (max-width: 640px) { .mice-slide { width: 160px; height: 100px; } }
        @media (prefers-reduced-motion: reduce) { .mice-track { animation: none; } .mice-slider { overflow-x: auto; } }
    </style>
</head>
<body class="island-page mice-page">
    <a class="skip-link" href="#main">Skip to content</a>

    @include('partials.site-header')

    <main id="main">
        <section class="island-hero" aria-labelledby="mice-title">
            <img class="island-hero-image" src="{{ $page->imageUrl($page->hero_image) }}" alt="" aria-hidden="true">
            <div class="island-hero-shade"></div>
            <div class="island-hero-content">
                <span class="island-eyebrow">{{ $page->hero_eyebrow }}</span>
                <h1 id="mice-title">{{ $page->hero_title }}<br><strong>{{ $page->hero_highlight }}</strong></h1>
                <p>{{ $page->hero_body }}</p>
                <a class="island-cta" href="#contact">{{ $page->hero_cta_label }} <span aria-hidden="true">↓</span></a>
            </div>
            <span class="island-hero-note">GOVERNMENT · PRIVATE SECTOR · CUSTOMISED</span>
        </section>

        <section class="island-intro" aria-labelledby="mice-intro-title">
            <div class="island-intro-copy">
                <span class="island-section-label">{{ $page->intro_eyebrow }}</span>
                <h2 id="mice-intro-title">{{ $page->intro_heading }} <span>{{ $page->intro_highlight }}</span></h2>
                @foreach (preg_split('/\R{2,}/', trim($page->intro_body)) as $paragraph)
                    <p>{{ $paragraph }}</p>
                @endforeach
            </div>
            <div class="island-photo-stack">
                <img src="{{ $page->imageUrl($page->intro_image) }}" alt="{{ $page->intro_image_alt }}" loading="lazy" decoding="async">
                <span class="island-photo-caption">{{ $page->intro_image_caption }}</span>
            </div>
        </section>

        <section class="island-highlights" aria-labelledby="mice-why-title">
            <div class="island-section-heading">
                <span class="island-section-label">{{ $page->why_eyebrow }}</span>
                <h2 id="mice-why-title">{{ $page->why_heading }}</h2>
            </div>
            <div class="island-highlight-grid">
                @foreach ($page->highlights as $highlight)
                    <article class="island-highlight">
                        <span class="island-highlight-number">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                        <h3>{{ $highlight['title'] }}</h3>
                        <p>{{ $highlight['body'] }}</p>
                    </article>
                @endforeach
            </div>
        </section>

        @if ($galleryImages->isNotEmpty())
            <section class="mice-gallery" aria-labelledby="mice-gallery-title">
                <span class="mice-eyebrow">OUR MOMENTS</span>
                <h2 id="mice-gallery-title">Gallery</h2>
                <p class="mice-sub">A glimpse of the corporate journeys, team-building days and incentive trips we have crafted for our clients.</p>
                <div class="mice-gallery-slider">
                    <div class="mice-gallery-track">
                        @foreach ([1, 2] as $copy)
                            @foreach ($galleryImages as $photo)
                                <figure class="mice-gallery-slide" @if ($copy === 2) aria-hidden="true" @endif>
                                    <img src="{{ $photo->imageUrl() }}" alt="{{ $copy === 1 ? $photo->caption : '' }}">
                                    @if ($photo->caption !== '')
                                        <figcaption>{{ $photo->caption }}</figcaption>
                                    @endif
                                </figure>
                            @endforeach
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        <section class="island-contact" id="contact" aria-labelledby="mice-contact-title">
            <div>
                <span class="island-section-label">{{ $page->contact_eyebrow }}</span>
                <h2 id="mice-contact-title">{{ $page->contact_heading }}</h2>
                <p>{{ $page->contact_body }}</p>
                <p>
                    <a href="tel:{{ preg_replace('/[^0-9+]/', '', $page->phone_primary) }}">{{ $page->phone_primary }}</a> /
                    <a href="tel:{{ preg_replace('/[^0-9+]/', '', $page->phone_secondary) }}">{{ $page->phone_secondary }}</a><br>
                    <a href="mailto:{{ $page->email }}">{{ $page->email }}</a>
                </p>
            </div>
            <a class="island-cta" href="https://wa.me/{{ $page->whatsapp_number }}?text={{ rawurlencode("Hi, I'd like a quotation for a company trip (MICE).") }}" target="_blank" rel="noreferrer">WhatsApp us <span aria-hidden="true">↗</span></a>
        </section>
        <section class="mice-clientele" aria-labelledby="mice-clientele-title">
            <span class="mice-eyebrow">{{ $page->clientele_eyebrow }}</span>
            <h2 id="mice-clientele-title">{{ $page->clientele_heading }}</h2>
            <p class="mice-sub">{{ $page->clientele_body }}</p>
            <div class="mice-slider">
                <div class="mice-track">
                    @foreach ([1, 2] as $copy)
                        @foreach ($clients as $client)
                            <div class="mice-slide" @if ($copy === 2) aria-hidden="true" @endif>
                                <img src="{{ $client->logoUrl() }}" alt="{{ $copy === 1 ? $client->name : '' }}" loading="lazy">
                            </div>
                        @endforeach
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
                <a href="{{ route('china-muslim') }}">China Muslim</a>
                <a href="{{ route('tour-package-private') }}">Private Tours</a>
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
