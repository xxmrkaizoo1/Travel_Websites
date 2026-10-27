<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#ffffff">
    <meta name="description" content="{{ $destination->meta_description }}">
    <title>{{ $destination->name }} | Group Tours | Smart Holidays</title>
    <style>@view-transition { navigation: auto; }</style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="group-tours-page">
    <a class="skip-link" href="#main">Skip to content</a>

    @include('partials.site-header')

    <main id="main">
        <header class="focus-tours-hero focus-tours-hero--compact" style="--focus-hero-image: url('{{ $destination->imageUrl($destination->hero_image) }}')">
            <div class="focus-tours-hero__inner">
                <span class="focus-tours-hero__eyebrow">{{ $destination->hero_eyebrow }}</span>
                <h1 id="destination-title">{{ $destination->heading }}</h1>
                @if (filled($destination->intro))
                    <p>{{ $destination->intro }}</p>
                @endif
                <div class="focus-tours-hero__meta">
                    <span><strong>{{ count($destination->states ?? []) }}</strong> {{ $destination->placeTerm(count($destination->states ?? [])) }} to explore</span>
                    <a href="{{ route('group-tours') }}"><span aria-hidden="true">←</span> All destinations</a>
                </div>
            </div>
        </header>

        @php($regions = $destination->regionsInUse())
        @php($stateCount = count($destination->states ?? []))
        @php($isAdmin = (bool) auth()->user()?->is_admin)
        <section class="destination-states" aria-labelledby="destination-states-title">
            <div class="destination-states__inner">
                @if ($isAdmin && session('status'))
                    <p class="destination-admin-status" role="status">{{ session('status') }}</p>
                @endif
                <div class="destination-states__heading">
                    <h2 id="destination-states-title">{{ $destination->states_heading }}</h2>
                    @if ($stateCount > 0)
                        <div class="destination-finder" data-state-finder data-item-singular="{{ $destination->placeTerm() }}" data-item-plural="{{ $destination->placeTerm(2) }}">
                            <label class="focus-tours__search">
                                <span class="visually-hidden">Search {{ $destination->placeTerm(2) }}</span>
                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="2"/><path d="m20 20-3.5-3.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                                <input type="search" placeholder="Search {{ $destination->placeTerm(2) }} or cities…" autocomplete="off" data-state-search>
                            </label>
                            @if (count($regions) > 1)
                                <label class="destination-finder__region">
                                    <span class="visually-hidden">Filter by region</span>
                                    <select data-state-region>
                                        <option value="">All regions</option>
                                        @foreach ($regions as $regionKey => $regionLabel)
                                            <option value="{{ $regionKey }}">{{ $regionLabel }}</option>
                                        @endforeach
                                    </select>
                                </label>
                            @endif
                            <p class="focus-tours__count" aria-live="polite" data-state-count>{{ $stateCount }} {{ $destination->placeTerm($stateCount) }}</p>
                        </div>
                    @endif

                    @if (count($regions) > 1)
                        <div class="destination-region-slides">
                            <div class="focus-tours__slider destination-region-slider" data-card-slider>
                                <button class="focus-tours__arrow focus-tours__arrow--prev" type="button" aria-label="Previous regions" data-slider-prev hidden><span aria-hidden="true">‹</span></button>
                                <nav class="focus-tours__track" aria-label="Regions" data-slider-track>
                                    @foreach ($regions as $regionKey => $regionLabel)
                                        @php($regionStates = array_column(array_filter($destination->states, fn (array $state): bool => $state['region'] === $regionKey), 'name'))
                                        @php($cover = $destination->regionCover($regionKey))
                                        <a class="destination-region-slide" href="#region-{{ $regionKey }}" data-region-slide="{{ $regionKey }}">
                                            <img src="{{ $cover['image'] }}" alt="{{ $cover['alt'] }}" loading="lazy">
                                            <span class="destination-region-slide__count"><strong>{{ count($regionStates) }}</strong> {{ $destination->placeTerm(count($regionStates)) }}</span>
                                            <span class="destination-region-slide__body">
                                                <strong>{{ $regionLabel }}</strong>
                                                <small>{{ implode(' · ', $regionStates) }}</small>
                                            </span>
                                        </a>
                                    @endforeach
                                </nav>
                                <button class="focus-tours__arrow focus-tours__arrow--next" type="button" aria-label="Next regions" data-slider-next hidden><span aria-hidden="true">›</span></button>
                            </div>
                            <div class="focus-tours__dots" data-slider-dots hidden></div>
                        </div>
                    @endif
                </div>

                @forelse ($regions as $regionKey => $regionLabel)
                    <section class="destination-region" id="region-{{ $regionKey }}" aria-labelledby="region-{{ $regionKey }}-title" data-region-section>
                        <div class="destination-region__heading">
                            <h3 id="region-{{ $regionKey }}-title">{{ $regionLabel }} <span>{{ $destination->name }}</span></h3>
                            @if ($isAdmin && $stateCount < \App\Models\GroupTourDestination::MAX_STATES)
                                <a class="destination-admin-add" href="{{ route('admin.group-tour-destinations.states.create', [$destination, 'region' => $regionKey]) }}"><span aria-hidden="true">＋</span> Add {{ $destination->placeTerm() }}</a>
                            @endif
                        </div>
                        <div class="destination-state-grid">
                            @foreach (array_filter($destination->states, fn (array $state): bool => $state['region'] === $regionKey) as $state)
                                <article class="destination-state region-{{ $regionKey }} region-tone-{{ array_search($regionKey, array_keys($destination->regionOptions()), true) % 5 }}{{ filled($state['image']) ? ' has-photo' : '' }}" id="{{ \App\Models\GroupTourDestination::stateAnchor($state['name']) }}" data-state-card data-region="{{ $regionKey }}" data-search="{{ \Illuminate\Support\Str::lower(implode(' ', [$state['name'], $state['capital'], $state['tagline'], $regionLabel])) }}">
                                    @if (filled($state['image']))
                                        <img src="{{ $destination->imageUrl($state['image']) }}" alt="{{ $state['alt'] ?: $state['name'].', '.$destination->name }}" loading="lazy">
                                    @else
                                        <span class="destination-state__art" aria-hidden="true">{{ \Illuminate\Support\Str::of($state['name'])->explode(' ')->map(fn (string $word): string => \Illuminate\Support\Str::substr($word, 0, 1))->take(2)->implode('') }}</span>
                                    @endif
                                    @if ($isAdmin)
                                        <div class="destination-state__admin">
                                            <a href="{{ route('admin.group-tour-destinations.states.edit', [$destination, \Illuminate\Support\Str::slug($state['name'])]) }}" aria-label="Edit {{ $state['name'] }}">Edit</a>
                                            <form method="POST" action="{{ route('admin.group-tour-destinations.states.destroy', [$destination, \Illuminate\Support\Str::slug($state['name'])]) }}" data-confirm="Remove {{ $state['name'] }} from this page?">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" aria-label="Remove {{ $state['name'] }}">Remove</button>
                                            </form>
                                        </div>
                                    @endif
                                    <div class="destination-state__body">
                                        @if (filled($state['capital']))
                                            <span class="destination-state__capital">{{ $destination->placeBadge($state['capital']) }}</span>
                                        @endif
                                        <h4>{{ $state['name'] }}</h4>
                                        @if (filled($state['tagline']))
                                            <p>{{ $state['tagline'] }}</p>
                                        @endif
                                        <a class="destination-state__enquire" href="{{ $page->tourEnquiryUrl($state['name'].', '.$destination->name) }}" target="_blank" rel="noreferrer" aria-label="Enquire on WhatsApp about group tours to {{ $state['name'] }}">Enquire <span aria-hidden="true">↗</span></a>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </section>
                @empty
                    <p class="destination-states__empty">{{ \Illuminate\Support\Str::ucfirst($destination->placeTerm(2)) }} for {{ $destination->name }} are coming soon. Ask us on WhatsApp for current group departures.</p>
                    @if ($isAdmin)
                        <p class="destination-states__empty"><a class="destination-admin-add" href="{{ route('admin.group-tour-destinations.states.create', $destination) }}"><span aria-hidden="true">＋</span> Add the first {{ $destination->placeTerm() }}</a></p>
                    @endif
                @endforelse

                <p class="focus-tours__empty" data-state-empty hidden>
                    No {{ $destination->placeTerm(2) }} match your search. <button type="button" data-state-reset>Show all {{ $destination->placeTerm(2) }}</button>
                </p>
            </div>
        </section>

        <section class="focus-tours-cta" aria-labelledby="focus-tours-cta-title">
            <div class="focus-tours-cta__inner">
                <div>
                    <h2 id="focus-tours-cta-title">Planning a group trip in {{ $destination->name }}?</h2>
                    <p>Tell us which {{ $destination->placeTerm(2) }} you would like to visit and our team will plan the route, stays and transport for your group.</p>
                </div>
                <a class="focus-tours-cta__button" href="{{ $page->tourEnquiryUrl($destination->name) }}" target="_blank" rel="noreferrer">Ask us on WhatsApp <span aria-hidden="true">↗</span></a>
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
