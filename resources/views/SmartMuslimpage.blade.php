<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#ffffff">
    <meta name="description" content="Discover thoughtful Muslim-friendly tours and meaningful holidays with Smart Holidays by Roystar Travel & Tours.">
    <title>Smart Muslim by Roystar Travel &amp; Tours</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>@view-transition { navigation: auto; }</style>
</head>
<body class="smart-muslim-page">
    <a class="skip-link" href="#main">Skip to content</a>

    @include('partials.site-header')

    <a class="admin-portal-fab" href="{{ route('login') }}" aria-label="Open admin login" title="Admin login">
        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M4 20V7.5L12 4l8 3.5V20H4Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
            <path d="M9 20v-5a3 3 0 0 1 6 0v5M8 9h.01M12 9h.01M16 9h.01" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
        </svg>
    </a>

    <main id="main">
        <section class="campaign" id="home" aria-label="Featured Smart Holidays campaigns">
            <div class="carousel" aria-roledescription="carousel" aria-label="Featured travel offers">
                <div class="carousel-track">
                    @foreach ($campaigns as $index => $campaign)
                    <article class="campaign-slide slide-{{ $campaign->design_key }}{{ $campaign->isBanner() ? ' campaign-slide-banner' : '' }}{{ $loop->first ? ' is-active' : '' }}" aria-roledescription="slide" aria-label="{{ $index + 1 }} of {{ $campaigns->count() }}" @if (! $loop->first) aria-hidden="true" inert @endif>
                        @if ($campaign->isBanner())
                        <img class="banner-image" src="{{ $campaign->imageUrl($campaign->image_main) }}" alt="{{ $campaign->image_alt }}" @if ($loop->first) fetchpriority="high" @else loading="lazy" @endif>
                        <div class="banner-message">
                            <span class="campaign-kicker">{{ $campaign->kicker }}</span>
                            @if ($loop->first)
                                <h1>{{ $campaign->headline }}<br><strong>{{ $campaign->headline_highlight }}</strong></h1>
                            @else
                                <h2>{{ $campaign->headline }}<br><strong>{{ $campaign->headline_highlight }}</strong></h2>
                            @endif
                            <p class="campaign-description">{{ $campaign->body }}</p>
                            <a href="#contact" class="campaign-cta">{{ $campaign->cta_label }} <span aria-hidden="true">↗</span></a>
                        </div>
                        @else
                        <div class="campaign-collage">
                            <img class="collage-main" src="{{ $campaign->imageUrl($campaign->image_main) }}" alt="{{ $campaign->image_alt }}" @if ($loop->first) fetchpriority="high" @else loading="lazy" @endif>
                            <img class="collage-small collage-small-top" src="{{ $campaign->imageUrl($campaign->image_top) }}" alt="" aria-hidden="true" loading="lazy">
                            <img class="collage-small collage-small-bottom" src="{{ $campaign->imageUrl($campaign->image_bottom) }}" alt="" aria-hidden="true" loading="lazy">
                            <span class="collage-sticker">{{ $campaign->sticker }}</span>
                            <span class="collage-ribbon">{{ $campaign->ribbon }}</span>
                        </div>
                        <div class="campaign-message">
                            <span class="campaign-kicker">{{ $campaign->kicker }}</span>
                            @if ($loop->first)
                                <h1>{{ $campaign->headline }}<br><strong>{{ $campaign->headline_highlight }}</strong></h1>
                            @else
                                <h2>{{ $campaign->headline }}<br><strong>{{ $campaign->headline_highlight }}</strong></h2>
                            @endif
                            <span class="campaign-rule"></span>
                            <p class="campaign-description">{{ $campaign->body }}</p>
                            <a href="#contact" class="campaign-cta">{{ $campaign->cta_label }} <span aria-hidden="true">↗</span></a>
                        </div>
                        <span class="campaign-brand-stamp" aria-hidden="true">{{ $campaign->brand_top }}<br><b>{{ $campaign->brand_bottom }}</b></span>
                        @endif
                    </article>
                    @endforeach
                </div>
                <button class="carousel-arrow carousel-prev" type="button" aria-label="Previous promotion"><span aria-hidden="true">‹</span></button>
                <button class="carousel-arrow carousel-next" type="button" aria-label="Next promotion"><span aria-hidden="true">›</span></button>
            </div>
            <div class="carousel-dots" role="group" aria-label="Choose a promotion">
                @foreach ($campaigns as $index => $campaign)
                    <button type="button" class="carousel-dot{{ $loop->first ? ' is-active' : '' }}" aria-label="Show promotion {{ $index + 1 }}" @if ($loop->first) aria-current="true" @endif></button>
                @endforeach
            </div>
        </section>


        <section class="welcome-section" id="welcome" aria-labelledby="welcome-title">
            <h2 id="welcome-title">WELCOME TO SMART MUSLIM BY ROYSTAR TRAVEL TOURS</h2>
            <p class="welcome-subtitle">Muslim division of Roystar Travel &amp; Tours</p>

            <div class="values-grid">
                <article class="value-card">
                    <span class="value-icon" aria-hidden="true">
                        <svg viewBox="0 0 60 60" fill="none"><path d="M7 26c9-2 14-9 18-18 4 8 8 14 17 16-9 0-14 3-18 9-5-5-10-7-17-7Z" stroke="currentColor" stroke-width="2"/><path d="M15 35c11-4 22-4 31 1M14 40c12-3 23-2 32 2" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><path d="M24 28v17m-7-6c4 3 10 4 16 3" stroke="#8dbfff" stroke-width="1.5" stroke-linecap="round"/></svg>
                    </span>
                    <h3>Jenama Kukuh</h3>
                    <p>Smart Muslim oleh Roystar Travel Tours telah beroperasi sejak 1994</p>
                </article>
                <article class="value-card">
                    <span class="value-icon" aria-hidden="true">
                        <svg viewBox="0 0 60 60" fill="none"><path d="m30 4 5 5 8-.3 2.4 7.6 6.8 4.2-2 7.7 2 7.7-6.8 4.2L43 48.7l-8-.3-5 5-5-5-8 .3-2.4-7.6-6.8-4.2 2-7.7-2-7.7 6.8-4.2L17 8.7l8 .3 5-5Z" stroke="currentColor" stroke-width="1.8"/><circle cx="30" cy="30" r="12" stroke="currentColor" stroke-width="1.6"/><path d="m30 20 2.3 6.4 6.7.2-5.3 4 1.9 6.4-5.6-3.8-5.6 3.8 1.9-6.4-5.3-4 6.7-.2L30 20Z" stroke="currentColor" stroke-width="1.2"/></svg>
                    </span>
                    <h3>Mesra Muslim</h3>
                    <p>Kami serius tentang makanan halal dan destinasi mesra Muslim</p>
                </article>
                <article class="value-card">
                    <span class="value-icon" aria-hidden="true">
                        <svg viewBox="0 0 60 60" fill="none"><path d="M7 40.5 23 35l8 2.5c2.4.8 2.3 4.1-.2 4.6l-8.6 1.7 13.5 5.1a4 4 0 0 0 3.7-.6l12-9.2a3 3 0 0 0-3.2-5l-9 4.4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M36 29V17h7v12m-11-5 7-7 7 7M12 38l-3 9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><circle cx="39.5" cy="11.5" r="7.5" fill="white" stroke="currentColor" stroke-width="1.7"/><path d="M39.5 7v9m-2-7h3.2a1.8 1.8 0 0 1 0 3.6h-3.2" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/></svg>
                    </span>
                    <h3>Harga Berpatutan</h3>
                    <p>Harga pakej berpatutan mengikut harga pasaran semasa</p>
                </article>
                <article class="value-card">
                    <span class="value-icon" aria-hidden="true">
                        <svg viewBox="0 0 60 60" fill="none"><circle cx="30" cy="12" r="5" stroke="currentColor" stroke-width="1.8"/><circle cx="13" cy="17" r="4" stroke="currentColor" stroke-width="1.7"/><circle cx="47" cy="17" r="4" stroke="currentColor" stroke-width="1.7"/><path d="M21 46V32a9 9 0 0 1 18 0v14m-26 0V32a7 7 0 0 1 10-6.3M47 46V32a7 7 0 0 0-10-6.3M25 47h10m-25 0h8m24 0h8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><path d="M30 24v19m-4-15 4 3 4-3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                    <h3>Untuk Semua</h3>
                    <p>Bukan sahaja Muslim, malah sesuai untuk semua lapisan umur &amp; masyarakat</p>
                </article>
            </div>
        </section>
    </main>

    <section class="services-section" aria-labelledby="services-title">
        <div class="services-inner">
            <h2 id="services-title">{{ $servicesHeading }}</h2>
            <div class="services-bento">
                @foreach ($services as $service)
                    @php($opensNewTab = str_starts_with($service->href, 'https://'))
                    <a class="service-tile" id="{{ $service->design_key }}" href="{{ $service->href }}" @if ($opensNewTab) target="_blank" rel="noreferrer" @endif>
                        <img src="{{ $service->imageUrl() }}" alt="{{ $service->image_alt }}" loading="lazy">
                        <span class="service-tile-number" aria-hidden="true">{{ sprintf('%02d', $loop->iteration) }}</span>
                        <span class="service-tile-body">
                            @if (\Illuminate\Support\Str::squish(\Illuminate\Support\Str::lower($service->overlay_label)) !== \Illuminate\Support\Str::squish(\Illuminate\Support\Str::lower($service->title)))
                                <span class="service-tile-title">{{ $service->title }}</span>
                            @endif
                            <strong class="service-tile-label">{{ $service->overlay_label }}</strong>
                            <span class="service-tile-button">{{ $service->button_label }} <span aria-hidden="true">{{ $opensNewTab ? '↗' : '→' }}</span></span>
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    @if ($consultants->isNotEmpty())
        <section class="consultants-section" aria-labelledby="consultants-title">
            <div class="consultants-inner">
                <div class="consultants-intro">
                    <p class="consultants-kicker"><span aria-hidden="true">💬</span> Have a question?</p>
                    <h2 id="consultants-title">We’re here to help</h2>
                    <p>Chat with one of our travel consultants on WhatsApp about packages, departure dates or a custom trip for your group.</p>
                    <div class="consultants-direct">
                        <a href="tel:+60321430188"><span aria-hidden="true">☎</span> +603 2143 0188</a>
                        <a href="mailto:tour@roystartravel.com.my"><span aria-hidden="true">✉</span> tour@roystartravel.com.my</a>
                    </div>
                </div>

                <ul class="consultants-grid" role="list">
                    @foreach ($consultants as $consultant)
                        <li class="consultant-card" style="--consultant-delay: {{ $loop->index * 60 }}ms">
                            <span class="consultant-photo">
                                @if ($consultant->photoUrl())
                                    <img src="{{ $consultant->photoUrl() }}" alt="{{ $consultant->name }}" loading="lazy" width="88" height="88">
                                @else
                                    <span class="consultant-avatar" aria-hidden="true"><svg viewBox="0 0 24 24"><path fill="currentColor" d="M12 12a4.5 4.5 0 1 0 0-9 4.5 4.5 0 0 0 0 9Zm0 2c-4.4 0-8 2.5-8 5.6V21h16v-1.4C20 16.5 16.4 14 12 14Z"/></svg></span>
                                @endif                            </span>
                            <span class="consultant-body">
                                <strong>{{ $consultant->name }}</strong>
                                @if (filled($consultant->role))
                                    <small>{{ $consultant->role }}</small>
                                @endif
                            </span>
                            <a class="consultant-whatsapp" href="{{ $consultant->whatsappUrl() }}" target="_blank" rel="noreferrer" aria-label="Chat with {{ $consultant->name }} on WhatsApp">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M12.04 2a9.9 9.9 0 0 0-8.5 15l-1.4 5 5.2-1.36A9.9 9.9 0 1 0 12.04 2Zm0 18.1a8.2 8.2 0 0 1-4.2-1.15l-.3-.18-3.08.8.82-3-.2-.31a8.2 8.2 0 1 1 6.96 3.84Zm4.5-6.14c-.25-.12-1.46-.72-1.69-.8-.23-.08-.39-.12-.55.12-.17.25-.64.8-.78.97-.14.16-.29.18-.53.06a6.7 6.7 0 0 1-3.34-2.92c-.25-.43.25-.4.72-1.34.08-.16.04-.3-.02-.43l-.75-1.82c-.2-.48-.4-.41-.55-.42h-.47a.9.9 0 0 0-.65.3 2.74 2.74 0 0 0-.85 2.03 4.76 4.76 0 0 0 1 2.53 10.9 10.9 0 0 0 4.17 3.68c1.55.67 2.16.73 2.93.61.47-.07 1.46-.6 1.66-1.17.2-.58.2-1.07.15-1.17-.06-.1-.22-.17-.47-.29Z"/></svg>
                                <span>WhatsApp</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif

    <footer class="site-footer" id="contact">
        <div class="footer-inner">
            <a class="footer-logo" href="{{ route('smart-muslim') }}" aria-label="Smart Muslim home">
                <img src="{{ asset('images/smart-muslim-logo.png') }}" alt="Smart Muslim">
            </a>
            <div class="footer-company">
                <h2>ROYSTAR TRAVEL &amp; TOURS SDN BHD</h2>
                <p>No.16A &amp; 16B, Lorong Brunei 3,<br>Off Jalan Pudu, 55100 Kuala Lumpur,<br>WPKL, Malaysia.</p>
                <a href="tel:+60321430188">☎ Tour: +603 2143 0188 / 0288</a>
                <a href="https://wa.me/60165500590" target="_blank" rel="noreferrer">◉ WhatsApp: +6016 550 0590</a>
                <a href="https://wa.me/60165500281" target="_blank" rel="noreferrer">◉ WhatsApp: +6016 550 0281</a>
                <a href="mailto:tour@roystartravel.com.my">✉ tour@roystartravel.com.my</a>
                <a href="https://smartholidays.com.my/" target="_blank" rel="noreferrer">◎ www.smartholidays.com.my</a>
            </div>
            <div class="footer-links">
                <h2>Quick Links</h2>
                <a href="#welcome">About Us</a>
                <a href="{{ route('gallery') }}">Our Gallery</a>
                <a href="#services-title">Our Services</a>
                <a href="https://smartholidays.com.my/booking-conditions/" target="_blank" rel="noreferrer">Booking Conditions</a>
                <a href="{{ route('privacy-policy') }}">Privacy Policy</a>
                <h2 class="footer-follow-heading">Follow Us</h2>
                <a href="https://www.facebook.com/smartholidays.com.my/" target="_blank" rel="noreferrer">◉ Facebook</a>
                <a href="https://wa.me/60165500529" target="_blank" rel="noreferrer">◉ WhatsApp: +6016 550 0529</a>
            </div>
            <div class="footer-brands" aria-label="Our travel brands">
                <h2>Our brands</h2>
                <a href="{{ route('smart-muslim') }}" class="footer-brand-badge"><strong>SMART</strong><span>MUSLIM</span></a>
                <a href="{{ route('home') }}" class="footer-brand-badge footer-brand-main"><strong>SMART</strong><span>HOLIDAYS</span></a>
            </div>
            <div class="footer-map">
                <iframe
                    title="Map to Roystar Travel and Tours in Kuala Lumpur"
                    src="https://maps.google.com/maps?q=No.16A%20Lorong%20Brunei%203%2C%20Kuala%20Lumpur&t=&z=15&ie=UTF8&iwloc=&output=embed"
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                    allowfullscreen>
                </iframe>
            </div>
        </div>
        <div class="footer-bottom">
            © {{ date('Y') }} Smart Holidays. All rights reserved.
        </div>
    </footer>
</body>
</html>
