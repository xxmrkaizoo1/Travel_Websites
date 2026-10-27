<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#ffffff">
    <meta name="description" content="{{ $page->hero_body }}">
    <title>Island &amp; Honeymoon | Smart Muslim by Roystar Travel &amp; Tours</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>@view-transition { navigation: auto; }</style>
</head>
<body class="island-page">
    <a class="skip-link" href="#main">Skip to content</a>

    @include('partials.site-header')

    <main id="main" class="honeymoon-page">
        <section class="island-hero" aria-labelledby="island-title">
            <img class="island-hero-image" src="{{ $page->imageUrl($page->hero_image) }}" alt="" aria-hidden="true">
            <div class="island-hero-shade"></div>
            <div class="island-hero-content">
                <span class="island-eyebrow">{{ $page->hero_eyebrow }}</span>
                <h1 id="island-title">{{ $page->hero_headline }}<br><strong>{{ $page->hero_highlight }}</strong></h1>
                <p>{{ $page->hero_body }}</p>
                <div class="honeymoon-hero-actions">
                    <a class="island-cta" href="#contact">{{ $page->hero_cta_label }} <span aria-hidden="true">↗</span></a>
                    @if ($islandPackages->isNotEmpty())
                        <a class="honeymoon-hero-link" href="#island-packages">Browse islands <span aria-hidden="true">↓</span></a>
                    @endif
                </div>
                <ul class="honeymoon-hero-facts">
                    @if ($islandPackages->isNotEmpty())
                        <li><strong>{{ $islandPackages->count() }}</strong> island escapes</li>
                    @endif
                    <li><strong>Private</strong> just for two</li>
                    <li><strong>Muslim</strong> friendly</li>
                </ul>
            </div>
            <span class="island-hero-note">{{ $page->hero_note }}</span>
        </section>

        @if ($islandPackages->isNotEmpty())
            {{-- Slide cards for the pages under Island & Honeymoon in the menu (Krabi, Lombok, Bali, ...). --}}
            <section class="honeymoon-islands" id="island-packages" aria-labelledby="island-packages-title">
                <header class="honeymoon-islands-heading">
                    <div>
                        <span class="island-section-label">ISLAND ESCAPES</span>
                        <h2 id="island-packages-title">Choose your <span>island.</span></h2>
                        <p>Private island and honeymoon packages, each planned around the two of you.</p>
                    </div>
                    <div class="honeymoon-islands-arrows">
                        <button type="button" data-card-slider-prev aria-controls="island-packages-track" aria-label="Previous islands"><span aria-hidden="true">←</span></button>
                        <button type="button" data-card-slider-next aria-controls="island-packages-track" aria-label="Next islands"><span aria-hidden="true">→</span></button>
                    </div>
                </header>

                <div class="honeymoon-islands-slider" data-card-slider>
                    <ul class="honeymoon-islands-track" id="island-packages-track" data-card-slider-track tabindex="0" aria-label="Island packages">
                        @foreach ($islandPackages as $islandPackage)
                            @php($itineraries = $islandPackage->package_data['itineraries'] ?? [])
                            <li class="honeymoon-island-card">
                                <a class="honeymoon-island-link" href="{{ route('tour-package.show', $islandPackage->slug) }}">
                                    <img src="{{ $islandPackage->imageUrl($islandPackage->hero_image) }}" alt="" loading="lazy" decoding="async">
                                    <span class="honeymoon-island-number" aria-hidden="true">{{ sprintf('%02d', $loop->iteration) }}</span>
                                    <span class="honeymoon-island-body">
                                        @if (filled($itineraries[0]['duration'] ?? null))
                                            <span class="honeymoon-island-duration">From {{ $itineraries[0]['duration'] }}</span>
                                        @endif
                                        <strong>{{ $islandPackage->name }}</strong>
                                        <span class="honeymoon-island-summary">{{ \Illuminate\Support\Str::limit($islandPackage->hero_body, 110) }}</span>
                                        <span class="honeymoon-island-footer">
                                            <span>{{ count($itineraries) }} {{ \Illuminate\Support\Str::plural('itinerary', count($itineraries)) }}</span>
                                            <span class="honeymoon-island-explore">Explore <span aria-hidden="true">→</span></span>
                                        </span>
                                    </span>
                                </a>
                                @include('partials.like-button', ['likeKey' => \App\Support\PackageLikes::key($islandPackage), 'likeLabel' => $islandPackage->name, 'likeClass' => 'honeymoon-island-like'])
                            </li>
                        @endforeach
                    </ul>
                    <div class="honeymoon-islands-progress" aria-hidden="true"><span data-card-slider-progress></span></div>
                </div>
            </section>
        @endif

        <section class="island-intro" aria-labelledby="island-intro-title">
            <div class="island-intro-copy">
                <span class="island-section-label">{{ $page->intro_eyebrow }}</span>
                <h2 id="island-intro-title">{{ $page->intro_headline }} <span>{{ $page->intro_highlight }}</span></h2>
                <p>{{ $page->intro_body }}</p>
                <a class="island-text-link" href="{{ $page->intro_link_href }}" target="_blank" rel="noreferrer">{{ $page->intro_link_label }} <span aria-hidden="true">→</span></a>
            </div>
            <div class="island-photo-stack">
                <img src="{{ $page->imageUrl($page->intro_image) }}" alt="{{ $page->intro_image_alt }}" loading="lazy">
                <span class="island-photo-caption">{{ $page->intro_image_caption }}</span>
            </div>
        </section>

        @foreach ($destinations as $destination)
        <section class="bali-destination" aria-labelledby="destination-{{ $destination->slug }}-title">
            <div class="bali-destination-inner">
                <header class="bali-destination-heading">
                    <div>
                        <span class="island-section-label">DESTINATION SPOTLIGHT</span>
                        <h2 id="destination-{{ $destination->slug }}-title">{{ $destination->name }}, <span>{{ $destination->country }}</span></h2>
                    </div>
                </header>

                <aside class="bali-fact-card" aria-label="{{ $destination->slug === 'bali-indonesia' ? 'Bali destination facts' : $destination->name.' destination facts' }}">
                    <span class="bali-quote-mark" aria-hidden="true">“</span>
                    <div class="bali-fact-content">
                        <blockquote>
                            <p>{{ $destination->description }}</p>
                        </blockquote>
                        <dl class="bali-facts">
                            <div>
                                <dt>Country</dt>
                                <dd>{{ $destination->country }}</dd>
                            </div>
                            <div>
                                <dt>Known as</dt>
                                <dd>{{ $destination->known_as }}</dd>
                            </div>
                            <div>
                                <dt>Perfect for</dt>
                                <dd>{{ $destination->perfect_for }}</dd>
                            </div>
                        </dl>
                    </div>
                </aside>

                <div class="bali-destination-grid">
                    <div class="bali-benefits">
                        <h3>{{ $destination->benefits_heading }}</h3>
                        <div class="bali-benefit-list">
                            @foreach ($destination->benefits as $benefit)
                                <article class="bali-benefit">
                                    <span class="bali-benefit-icon" aria-hidden="true">{{ $benefit['icon'] }}</span>
                                    <div>
                                        <h4>{{ $benefit['title'] }}</h4>
                                        <p>{{ $benefit['body'] }}</p>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </div>

                    <figure class="bali-feature-photo">
                        <img src="{{ $destination->imageUrl($destination->feature_image) }}" alt="{{ $destination->feature_image_alt }}" loading="lazy" decoding="async">
                        <figcaption><span>{{ $destination->feature_caption }}</span><span aria-hidden="true">{{ mb_strtoupper($destination->name) }} · {{ mb_strtoupper($destination->country) }}</span></figcaption>
                    </figure>
                </div>

                <div class="bali-gallery" role="region" aria-roledescription="carousel" aria-label="{{ $destination->name }} travel slideshow" tabindex="0">
                    <div class="bali-slides">
                        @foreach ($destination->gallery as $index => $slide)
                            <figure class="bali-slide{{ $loop->first ? ' is-active' : '' }}" aria-roledescription="slide" aria-label="{{ $index + 1 }} of {{ count($destination->gallery) }}" @if (! $loop->first) aria-hidden="true" inert @else aria-hidden="false" @endif>
                                <img src="{{ $destination->imageUrl($slide['image']) }}" alt="{{ $slide['alt'] }}" @if ($loop->first) decoding="async" @else loading="lazy" decoding="async" @endif>
                                <figcaption><span>{{ $slide['eyebrow'] }}</span><strong>{{ $slide['title'] }}</strong></figcaption>
                            </figure>
                        @endforeach
                    </div>

                    <button class="bali-carousel-arrow bali-carousel-prev" type="button" aria-label="Previous slide"><span aria-hidden="true">←</span></button>
                    <button class="bali-carousel-arrow bali-carousel-next" type="button" aria-label="Next slide"><span aria-hidden="true">→</span></button>

                    <div class="bali-carousel-toolbar">
                        <button class="bali-carousel-toggle" type="button" aria-label="Pause slideshow" aria-pressed="false"><span aria-hidden="true">Ⅱ</span></button>
                        <div class="bali-slide-dots" role="group" aria-label="Choose a slide">
                            @foreach ($destination->gallery as $index => $slide)
                                <button class="{{ $loop->first ? 'is-active' : '' }}" type="button" aria-label="Show slide {{ $index + 1 }}: {{ $slide['title'] }}" @if ($loop->first) aria-current="true" @endif></button>
                            @endforeach
                        </div>
                        <span class="bali-slide-count" aria-live="off"><strong>01</strong><span>/ {{ str_pad((string) count($destination->gallery), 2, '0', STR_PAD_LEFT) }}</span></span>
                    </div>
                </div>
            </div>
        </section>
        @endforeach

        <section class="island-highlights" aria-labelledby="island-highlights-title">
            <div class="island-section-heading">
                <span class="island-section-label">{{ $page->highlights_eyebrow }}</span>
                <h2 id="island-highlights-title">{{ $page->highlights_headline }}</h2>
                <p>{{ $page->highlights_body }}</p>
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

        <section class="island-contact" id="contact" aria-labelledby="island-contact-title">
            <div>
                <span class="island-section-label">{{ $page->contact_eyebrow }}</span>
                <h2 id="island-contact-title">{{ $page->contact_headline }}</h2>
                <p>{{ $page->contact_body }}</p>
            </div>
            <a class="island-cta" href="{{ $page->contact_href }}" target="_blank" rel="noreferrer">{{ $page->contact_cta_label }} <span aria-hidden="true">↗</span></a>
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
                <a href="{{ route('home') }}#home">Home</a>
                <a href="{{ route('home') }}#services-title">Our Services</a>
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
