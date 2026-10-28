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
            <div class="carousel" aria-roledescription="carousel" aria-label="Featured travel offers" data-campaign-interval="7000">
                <div class="carousel-track">
                    @foreach ($campaigns as $index => $campaign)
                    @php
                        $campaignPoints = array_values(array_filter(array_map('trim', preg_split('/\R/', $campaign->body))));
                    @endphp
                    <article class="campaign-slide cs-slide slide-{{ $campaign->design_key }} {{ $campaign->isBanner() ? 'campaign-slide-banner' : 'cs-collage' }}{{ $loop->first ? ' is-active' : '' }}" aria-roledescription="slide" aria-label="{{ $index + 1 }} of {{ $campaigns->count() }}" @if (! $loop->first) aria-hidden="true" inert @endif>
                        <div class="cs-media">
                            <img class="cs-photo" src="{{ $campaign->imageUrl($campaign->image_main) }}" alt="{{ $campaign->image_alt }}" @if ($loop->first) fetchpriority="high" @else loading="lazy" @endif>
                        </div>
                        <div class="cs-shade" aria-hidden="true"></div>

                        <div class="cs-content">
                            <span class="cs-kicker"><i aria-hidden="true"></i>{{ $campaign->kicker }}</span>
                            @if ($loop->first)
                                <h1 class="cs-title">{{ $campaign->headline }} <strong>{{ $campaign->headline_highlight }}</strong></h1>
                            @else
                                <h2 class="cs-title">{{ $campaign->headline }} <strong>{{ $campaign->headline_highlight }}</strong></h2>
                            @endif
                            <ul class="cs-points" role="list">
                                @foreach ($campaignPoints as $point)
                                    <li>{{ $point }}</li>
                                @endforeach
                            </ul>
                            <a href="#contact" class="cs-cta">{{ $campaign->cta_label }} <span aria-hidden="true">→</span></a>
                        </div>

                        @unless ($campaign->isBanner())
                            <div class="cs-gallery" aria-hidden="true">
                                <span class="cs-card cs-card-top"><img src="{{ $campaign->imageUrl($campaign->image_top) }}" alt="" loading="lazy"></span>
                                <span class="cs-card cs-card-bottom"><img src="{{ $campaign->imageUrl($campaign->image_bottom) }}" alt="" loading="lazy"></span>
                                @if (filled($campaign->sticker))
                                    <span class="cs-sticker"><span>{!! nl2br(e($campaign->sticker)) !!}</span></span>
                                @endif
                            </div>
                            @if (filled($campaign->ribbon))
                                <span class="cs-ribbon">{{ $campaign->ribbon }}</span>
                            @endif
                            @if (filled($campaign->brand_top) || filled($campaign->brand_bottom))
                                <span class="cs-stamp" aria-hidden="true">{{ $campaign->brand_top }}<b>{{ $campaign->brand_bottom }}</b></span>
                            @endif
                        @endunless
                    </article>
                    @endforeach
                </div>

                @if ($campaigns->count() > 1)
                    <div class="cs-controls">
                        <span class="cs-counter" aria-hidden="true"><b data-campaign-current>01</b><span>/ {{ str_pad((string) $campaigns->count(), 2, '0', STR_PAD_LEFT) }}</span></span>
                        <div class="carousel-dots" role="group" aria-label="Choose a promotion">
                            @foreach ($campaigns as $index => $campaign)
                                <button type="button" class="carousel-dot{{ $loop->first ? ' is-active' : '' }}" aria-label="Show promotion {{ $index + 1 }}" @if ($loop->first) aria-current="true" @endif></button>
                            @endforeach
                        </div>
                        <div class="cs-arrows">
                            <button class="carousel-arrow carousel-prev" type="button" aria-label="Previous promotion"><span aria-hidden="true">←</span></button>
                            <button class="carousel-arrow carousel-next" type="button" aria-label="Next promotion"><span aria-hidden="true">→</span></button>
                        </div>
                    </div>
                @endif
            </div>
        </section>

        <section class="smx-about" id="welcome" aria-labelledby="welcome-title">
            <span class="smx-about-star" aria-hidden="true"></span>
            <div class="smx-about-inner">
                <div class="smx-about-copy" data-reveal>
                    <span class="smx-eyebrow">Muslim division of Roystar Travel &amp; Tours</span>
                    <h2 id="welcome-title">Welcome to <span>Smart Muslim</span></h2>
                    <p class="smx-lead">Since 1994 we have planned journeys for Muslim travellers: halal meals, prayer-friendly schedules and destinations chosen with care, for families, friends and groups of every age.</p>
                    <ul class="smx-facts" role="list">
                        <li><strong>1994</strong><span>Travelling with you since</span></li>
                        <li><strong>Halal</strong><span>Meals planned in</span></li>
                        <li><strong>All ages</strong><span>Families &amp; groups</span></li>
                    </ul>
                    <div class="smx-actions">
                        <a class="smx-btn smx-btn-primary" href="#services-title">Explore our services <span aria-hidden="true">↓</span></a>
                        <a class="smx-btn smx-btn-ghost" href="https://wa.me/60165500590" target="_blank" rel="noreferrer">Talk to our team <span aria-hidden="true">↗</span></a>
                    </div>
                </div>

                <ul class="smx-values" role="list" aria-label="Why travel with Smart Muslim">
                    <li class="smx-value" data-reveal>
                        <span class="smx-value-icon" aria-hidden="true">
                            <svg viewBox="0 0 60 60" fill="none"><path d="M7 26c9-2 14-9 18-18 4 8 8 14 17 16-9 0-14 3-18 9-5-5-10-7-17-7Z" stroke="currentColor" stroke-width="2"/><path d="M15 35c11-4 22-4 31 1M14 40c12-3 23-2 32 2" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><path d="M24 28v17m-7-6c4 3 10 4 16 3" stroke="currentColor" stroke-opacity=".55" stroke-width="1.5" stroke-linecap="round"/></svg>
                        </span>
                        <span class="smx-value-number" aria-hidden="true">01</span>
                        <h3>Trusted since 1994</h3>
                        <p>Smart Muslim by Roystar Travel &amp; Tours has been taking travellers abroad for over three decades.</p>
                    </li>
                    <li class="smx-value" data-reveal>
                        <span class="smx-value-icon" aria-hidden="true">
                            <svg viewBox="0 0 60 60" fill="none"><path d="m30 4 5 5 8-.3 2.4 7.6 6.8 4.2-2 7.7 2 7.7-6.8 4.2L43 48.7l-8-.3-5 5-5-5-8 .3-2.4-7.6-6.8-4.2 2-7.7-2-7.7 6.8-4.2L17 8.7l8 .3 5-5Z" stroke="currentColor" stroke-width="1.8"/><circle cx="30" cy="30" r="12" stroke="currentColor" stroke-width="1.6"/><path d="m30 20 2.3 6.4 6.7.2-5.3 4 1.9 6.4-5.6-3.8-5.6 3.8 1.9-6.4-5.3-4 6.7-.2L30 20Z" stroke="currentColor" stroke-width="1.2"/></svg>
                        </span>
                        <span class="smx-value-number" aria-hidden="true">02</span>
                        <h3>Muslim-friendly</h3>
                        <p>We take halal meals and Muslim-friendly destinations seriously, with prayer times planned into every day.</p>
                    </li>
                    <li class="smx-value" data-reveal>
                        <span class="smx-value-icon" aria-hidden="true">
                            <svg viewBox="0 0 60 60" fill="none"><path d="M7 40.5 23 35l8 2.5c2.4.8 2.3 4.1-.2 4.6l-8.6 1.7 13.5 5.1a4 4 0 0 0 3.7-.6l12-9.2a3 3 0 0 0-3.2-5l-9 4.4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M36 29V17h7v12m-11-5 7-7 7 7M12 38l-3 9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><circle cx="39.5" cy="11.5" r="7.5" stroke="currentColor" stroke-width="1.7"/><path d="M39.5 7v9m-2-7h3.2a1.8 1.8 0 0 1 0 3.6h-3.2" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/></svg>
                        </span>
                        <span class="smx-value-number" aria-hidden="true">03</span>
                        <h3>Fair prices</h3>
                        <p>Package prices that follow current market rates, with everything included spelled out clearly.</p>
                    </li>
                    <li class="smx-value" data-reveal>
                        <span class="smx-value-icon" aria-hidden="true">
                            <svg viewBox="0 0 60 60" fill="none"><circle cx="30" cy="12" r="5" stroke="currentColor" stroke-width="1.8"/><circle cx="13" cy="17" r="4" stroke="currentColor" stroke-width="1.7"/><circle cx="47" cy="17" r="4" stroke="currentColor" stroke-width="1.7"/><path d="M21 46V32a9 9 0 0 1 18 0v14m-26 0V32a7 7 0 0 1 10-6.3M47 46V32a7 7 0 0 0-10-6.3M25 47h10m-25 0h8m24 0h8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><path d="M30 24v19m-4-15 4 3 4-3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>
                        <span class="smx-value-number" aria-hidden="true">04</span>
                        <h3>For everyone</h3>
                        <p>Not only for Muslim travellers: our trips suit every age and every community.</p>
                    </li>
                </ul>
            </div>
        </section>

        <section class="smx-services" aria-labelledby="services-title">
            <div class="smx-services-inner">
                <header class="smx-section-heading" data-reveal>
                    <span class="smx-eyebrow">What we offer</span>
                    <h2 id="services-title">{{ $servicesHeading }}</h2>
                    <p>Pick a service to explore the journeys we plan for Muslim travellers.</p>
                </header>
                <div class="smx-service-grid">
                    @foreach ($services as $service)
                        @php
                            $opensNewTab = str_starts_with($service->href, 'https://');
                            $showsTitle = \Illuminate\Support\Str::squish(\Illuminate\Support\Str::lower($service->overlay_label)) !== \Illuminate\Support\Str::squish(\Illuminate\Support\Str::lower($service->title));
                        @endphp
                        <a class="smx-service" id="{{ $service->design_key }}" href="{{ $service->href }}" @if ($opensNewTab) target="_blank" rel="noreferrer" @endif data-reveal>
                            <img src="{{ $service->imageUrl() }}" alt="{{ $service->image_alt }}" loading="lazy" decoding="async">
                            <span class="smx-service-number" aria-hidden="true">{{ sprintf('%02d', $loop->iteration) }}</span>
                            <span class="smx-service-body">
                                @if ($showsTitle)
                                    <span class="smx-service-title">{{ $service->title }}</span>
                                @endif
                                <strong class="smx-service-label">{{ $service->overlay_label }}</strong>
                                <span class="smx-service-button">{{ $service->button_label }} <span aria-hidden="true">{{ $opensNewTab ? '↗' : '→' }}</span></span>
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>

        @if ($consultants->isNotEmpty())
            <section class="smx-help" aria-labelledby="consultants-title">
                <div class="smx-help-inner">
                    <div class="smx-help-intro" data-reveal>
                        <span class="smx-eyebrow">Have a question?</span>
                        <h2 id="consultants-title">We’re here to help</h2>
                        <p>Chat with one of our travel consultants on WhatsApp about packages, departure dates or a custom trip for your group.</p>
                        <div class="smx-help-direct">
                            <a href="tel:+60321430188"><span aria-hidden="true">☎</span> +603 2143 0188</a>
                            <a href="mailto:tour@roystartravel.com.my"><span aria-hidden="true">✉</span> tour@roystartravel.com.my</a>
                        </div>
                    </div>

                    <ul class="smx-consultants" role="list">
                        @foreach ($consultants as $consultant)
                            <li class="smx-consultant" data-reveal>
                                <span class="smx-consultant-photo">
                                    @if ($consultant->photoUrl())
                                        <img src="{{ $consultant->photoUrl() }}" alt="{{ $consultant->name }}" loading="lazy" width="96" height="96">
                                    @else
                                        <span class="smx-consultant-avatar" aria-hidden="true"><svg viewBox="0 0 24 24"><path fill="currentColor" d="M12 12a4.5 4.5 0 1 0 0-9 4.5 4.5 0 0 0 0 9Zm0 2c-4.4 0-8 2.5-8 5.6V21h16v-1.4C20 16.5 16.4 14 12 14Z"/></svg></span>
                                    @endif
                                    <i class="smx-consultant-status" aria-hidden="true"></i>
                                </span>
                                <strong>{{ $consultant->name }}</strong>
                                @if (filled($consultant->role))
                                    <small>{{ $consultant->role }}</small>
                                @endif
                                <a class="smx-consultant-whatsapp" href="{{ $consultant->whatsappUrl() }}" target="_blank" rel="noreferrer" aria-label="Chat with {{ $consultant->name }} on WhatsApp">
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M12.04 2a9.9 9.9 0 0 0-8.5 15l-1.4 5 5.2-1.36A9.9 9.9 0 1 0 12.04 2Zm0 18.1a8.2 8.2 0 0 1-4.2-1.15l-.3-.18-3.08.8.82-3-.2-.31a8.2 8.2 0 1 1 6.960 3.84Zm4.5-6.14c-.25-.12-1.46-.72-1.690-.8-.23-.08-.39-.12-.55.12-.17.25-.64.8-.78.97-.14.16-.29.18-.53.06a6.7 6.7 0 0 1-3.34-2.92c-.25-.43.25-.4.72-1.34.08-.16.04-.3-.02-.43l-.75-1.82c-.2-.48-.4-.41-.55-.42h-.47a.9.9 0 0 0-.65.3 2.74 2.74 0 0 0-.85 2.030 4.76 4.76 0 0 0 1 2.53 10.9 10.9 0 0 0 4.17 3.68c1.55.67 2.16.73 2.93.61.47-.07 1.46-.6 1.66-1.17.2-.58.2-1.07.15-1.17-.06-.1-.22-.17-.47-.29Z"/></svg>
                                    <span>WhatsApp</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </section>
        @endif

        <section class="smx-cta" aria-labelledby="smx-cta-title">
            <div class="smx-cta-inner" data-reveal>
                <span class="smx-cta-star" aria-hidden="true"></span>
                <div>
                    <span class="smx-eyebrow">Your next journey</span>
                    <h2 id="smx-cta-title">Travel the world, the halal way.</h2>
                    <p>Tell us where you dream of going and we’ll plan the meals, prayers and pace around you.</p>
                </div>
                <div class="smx-cta-actions">
                    <a class="smx-btn smx-btn-gold" href="https://wa.me/60165500590" target="_blank" rel="noreferrer">WhatsApp us <span aria-hidden="true">↗</span></a>
                    <a class="smx-btn smx-btn-ghost" href="{{ route('tour-package-private') }}">Browse private tours <span aria-hidden="true">→</span></a>
                </div>
            </div>
        </section>
    </main>

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
