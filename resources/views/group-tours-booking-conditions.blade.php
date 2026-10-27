<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#ffffff">
    <meta name="description" content="{{ $page->booking_intro ?: $page->meta_description }}">
    <title>{{ $page->booking_heading }} | Group Tours | Smart Holidays</title>
    <style>@view-transition { navigation: auto; }</style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="group-tours-page">
    <a class="skip-link" href="#main">Skip to content</a>

    @include('partials.site-header')

    <main id="main">
        <header class="focus-tours-hero focus-tours-hero--compact{{ filled($page->hero_video) ? ' has-video' : '' }}" style="--focus-hero-image: url('{{ $page->imageUrl($page->hero_image) }}')">
            @if (filled($page->hero_video))
                <video class="focus-tours-hero__video" src="{{ $page->imageUrl($page->hero_video) }}" poster="{{ $page->imageUrl($page->hero_image) }}" autoplay muted loop playsinline aria-hidden="true" data-background-video></video>
            @endif
            <div class="focus-tours-hero__inner">
                <span class="focus-tours-hero__eyebrow">{{ $page->hero_eyebrow }}</span>
                <h1 id="booking-conditions-title">{{ $page->booking_heading }}</h1>
                @if (filled($page->booking_intro))
                    <p>{{ $page->booking_intro }}</p>
                @endif
            </div>
        </header>

        @php
            $conditions = array_values($page->booking_conditions ?? []);
            $conditionIcons = [
                'deposit' => '📝', 'booking' => '📝', 'payment' => '💳', 'pay' => '💳', 'cancel' => '↩️', 'refund' => '↩️',
                'group' => '👥', 'minimum' => '👥', 'passport' => '🛂', 'visa' => '🛂', 'insurance' => '🛡️',
                'change' => '🔄', 'itinerary' => '🔄', 'baggage' => '🧳', 'luggage' => '🧳', 'child' => '🧒', 'health' => '🩺',
            ];
            $conditionIcon = fn (string $title): string => collect($conditionIcons)->first(fn (string $icon, string $word): bool => str_contains(\Illuminate\Support\Str::lower($title), $word)) ?? '📌';
            $conditionAnchor = fn (int $index, string $title): string => 'condition-'.($index + 1).'-'.\Illuminate\Support\Str::slug($title);
        @endphp
        <section class="booking-conditions" aria-labelledby="booking-conditions-title" data-booking-conditions>
            <div class="booking-layout">
                <aside class="booking-toc" aria-label="Booking conditions contents">
                    <div class="booking-toc__card">
                        <p class="booking-toc__label">On this page</p>
                        <p class="booking-toc__meta"><strong>{{ count($conditions) }}</strong> {{ \Illuminate\Support\Str::plural('condition', count($conditions)) }} · about {{ max(1, (int) ceil(str_word_count(implode(' ', array_column($conditions, 'body'))) / 200)) }} min read</p>
                        <nav>
                            <ol>
                                @foreach ($conditions as $index => $condition)
                                    <li><a href="#{{ $conditionAnchor($index, $condition['title']) }}" data-booking-toc-link><span>{{ sprintf('%02d', $index + 1) }}</span> {{ $condition['title'] }}</a></li>
                                @endforeach
                            </ol>
                        </nav>
                        <button class="booking-toc__print" type="button" data-print-page><span aria-hidden="true">🖨</span> Print or save as PDF</button>
                    </div>
                    <div class="booking-toc__help">
                        <span aria-hidden="true">💬</span>
                        <div>
                            <strong>Not sure about something?</strong>
                            <a href="{{ $page->whatsappUrl() }}" target="_blank" rel="noreferrer">Ask us on WhatsApp ↗</a>
                        </div>
                    </div>
                </aside>

                <ol class="booking-cards">
                    @foreach ($conditions as $index => $condition)
                        @php($points = array_values(array_filter(array_map('trim', preg_split('/\R/', $condition['body'])), 'filled')))
                        <li class="booking-card" id="{{ $conditionAnchor($index, $condition['title']) }}">
                            <div class="booking-card__head">
                                <span class="booking-card__icon" aria-hidden="true">{{ $conditionIcon($condition['title']) }}</span>
                                <div>
                                    <span class="booking-card__number">Condition {{ sprintf('%02d', $index + 1) }}</span>
                                    <h2>{{ $condition['title'] }}</h2>
                                </div>
                            </div>
                            @if (count($points) > 1)
                                <ul>
                                    @foreach ($points as $point)
                                        <li>{{ $point }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <p>{{ $points[0] ?? '' }}</p>
                            @endif
                        </li>
                    @endforeach
                </ol>
            </div>
        </section>

        <section class="focus-tours-cta" aria-labelledby="focus-tours-cta-title">
            <div class="focus-tours-cta__inner">
                <div>
                    <h2 id="focus-tours-cta-title">Have a question about booking?</h2>
                    <p>Our team is happy to explain any of these conditions before you book.</p>
                </div>
                <a class="focus-tours-cta__button" href="{{ $page->whatsappUrl() }}" target="_blank" rel="noreferrer">Ask us on WhatsApp <span aria-hidden="true">↗</span></a>
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
                <a href="{{ route('group-tours.booking-conditions') }}">Booking Conditions</a>
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
