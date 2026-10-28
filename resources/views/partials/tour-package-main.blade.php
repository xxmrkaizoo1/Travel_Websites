@php
    $slug = $page->slug;
    $slideCount = count($page->slides);
@endphp
<main id="{{ $seriesPageMainId ?? 'main' }}" @class(['package-page' => empty($page->package_data['series']), 'tour-main--series' => ! empty($page->package_data['series'])])>
    <section class="island-hero {{ $slug }}-hero" aria-labelledby="{{ $slug }}-title">
        <img class="island-hero-image" src="{{ $page->imageUrl($page->hero_image) }}" alt="" aria-hidden="true">
        <div class="island-hero-shade"></div>
        <div class="island-hero-content">
            <span class="island-eyebrow">{{ $page->hero_eyebrow }}</span>
            <h1 id="{{ $slug }}-title">{{ $page->hero_headline }}<br><strong>{{ $page->hero_highlight }}</strong></h1>
            <p>{{ $page->hero_body }}</p>
            <div class="package-hero-actions">
                <a class="island-cta" href="#contact">{{ $page->hero_cta_label }} <span aria-hidden="true">↗</span></a>
                @if (empty($page->package_data['series']))
                    @include('partials.like-button', ['likeKey' => \App\Support\PackageLikes::key($page), 'likeLabel' => $page->hero_headline.' '.$page->hero_highlight, 'likeClass' => 'package-like--hero'])
                @else
                    <a class="series-hero-secondary" href="#destinations">See destinations <span aria-hidden="true">↓</span></a>
                @endif
            </div>
            @if (! empty($page->package_data['series']))
                <ul class="series-hero-facts" role="list">
                    <li><strong>{{ count($page->package_data['series']) }}</strong><span>{{ \Illuminate\Support\Str::plural('destination', count($page->package_data['series'])) }}</span></li>
                    <li><strong>Fixed</strong><span>departure dates</span></li>
                    <li><strong>Guide</strong><span>&amp; transport included</span></li>
                </ul>
            @endif
        </div>
        @if (! empty($page->package_data['series']))
            <div class="series-hero-card">
                <span class="series-hero-flags" aria-hidden="true">
                    @foreach (array_slice($page->package_data['series'], 0, 5) as $destination)
                        <img src="{{ $page->imageUrl(filled($destination['flag'] ?? '') ? $destination['flag'] : \App\Models\TourPackagePage::PLACEHOLDER_IMAGE) }}" alt="" loading="lazy" style="--flag: {{ $loop->index }}">
                    @endforeach
                </span>
                <span class="series-hero-card-text">
                    <small>This year’s route</small>
                    <span class="island-hero-note">{{ $page->hero_note }}</span>
                </span>
            </div>
            <a class="series-hero-scroll" href="#destinations" aria-label="Scroll to destinations"><span aria-hidden="true"></span></a>
        @else
            <span class="island-hero-note">{{ $page->hero_note }}</span>
        @endif
    </section>

    <section class="island-intro" aria-labelledby="{{ $slug }}-intro-title">
        <div class="island-intro-copy">
            <span class="island-section-label">{{ $page->intro_eyebrow }}</span>
            <h2 id="{{ $slug }}-intro-title">{{ $page->intro_headline }} <span>{{ $page->intro_highlight }}</span></h2>
            <p>{{ $page->intro_body }}</p>
            <a class="island-text-link" href="#destinations">{{ $page->intro_link_label }} <span aria-hidden="true">→</span></a>
        </div>
        <div class="island-photo-stack {{ $slug }}-intro-photo">
            <img src="{{ $page->imageUrl($page->intro_image) }}" alt="{{ $page->intro_image_alt }}" loading="lazy">
            <span class="island-photo-caption">{{ $page->intro_image_caption }}</span>
        </div>
    </section>
    <section class="island-highlights" aria-labelledby="{{ $slug }}-highlights-title">
        <div class="island-section-heading">
            <span class="island-section-label">{{ $page->highlights_eyebrow }}</span>
            <h2 id="{{ $slug }}-highlights-title">{{ $page->highlights_headline }}</h2>
            <p>{{ $page->highlights_body }}</p>
        </div>
        <div class="island-highlight-grid {{ $slug }}-highlight-grid">
            @foreach ($page->highlights as $highlight)
                <article class="island-highlight">
                    <span class="island-highlight-number">{{ sprintf('%02d', $loop->iteration) }}</span>
                    <h3>{{ $highlight['title'] }}</h3>
                    <p>{{ $highlight['body'] }}</p>
                </article>
            @endforeach
        </div>
    </section>

    @if (! empty($page->package_data['series']))
        @include('Smart-Muslim.Tour-Package(series).tour-package-series', ['page' => $page, 'series' => $page->package_data['series']])
    @else
    <section class="destination-section {{ $slug }}-destinations" id="destinations" aria-labelledby="{{ $slug }}-destinations-title">
        <div class="island-section-heading">
            <span class="island-section-label">{{ $page->destinations_eyebrow }}</span>
            <h2 id="{{ $slug }}-destinations-title">{{ $page->destinations_headline }}</h2>
            <p>{{ $page->destinations_body }}</p>
        </div>
        <div class="pk-dest" role="region" aria-roledescription="carousel" aria-label="{{ $page->name }} destinations" data-pk-dest>
            <ol class="pk-dest-track" data-pk-dest-track>
                @foreach ($page->slides as $slide)
                    @php($hasLink = ! empty($slide['link_url']) && ! empty($slide['link_label']))
                    <li class="pk-dest-card" aria-roledescription="slide" aria-label="{{ $loop->iteration }} of {{ $slideCount }}" data-pk-dest-card>
                        <img src="{{ $page->imageUrl($slide['image']) }}" alt="{{ $slide['alt'] }}" loading="lazy" decoding="async" draggable="false">
                        <span class="pk-dest-number" aria-hidden="true">{{ sprintf('%02d', $loop->iteration) }}</span>
                        @if (filled($slide['eyebrow'] ?? ''))
                            <span class="pk-dest-eyebrow">{{ $slide['eyebrow'] }}</span>
                        @endif
                        <div class="pk-dest-body">
                            <h3>{{ $slide['title'] }}</h3>
                            <p>{{ $slide['body'] }}</p>
                            @if ($hasLink)
                                <a class="pk-dest-link" href="{{ $slide['link_url'] }}"@if (str_starts_with($slide['link_url'], 'http')) target="_blank" rel="noreferrer"@endif>{{ $slide['link_label'] }} <span aria-hidden="true">{{ str_starts_with($slide['link_url'], 'http') ? '↗' : '→' }}</span></a>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ol>
            <div class="pk-dest-controls">
                <p class="pk-dest-count" aria-live="polite" data-pk-dest-count><b>01</b> / {{ sprintf('%02d', $slideCount) }}</p>
                <span class="pk-dest-progress" aria-hidden="true"><i data-pk-dest-progress></i></span>
                <div class="pk-dest-buttons">
                    <button type="button" aria-label="Previous destinations" data-pk-dest-previous disabled>←</button>
                    <button type="button" aria-label="Next destinations" data-pk-dest-next>→</button>
                </div>
            </div>
        </div>
    </section>
    @endif

    <section class="island-contact {{ $slug }}-contact" id="contact" aria-labelledby="{{ $slug }}-contact-title">
        <div>
            <span class="island-section-label">{{ $page->contact_eyebrow }}</span>
            <h2 id="{{ $slug }}-contact-title">{{ $page->contact_headline }}</h2>
            <p>{{ $page->contact_body }}</p>
        </div>
        <a class="island-cta" href="{{ $page->contact_href }}" target="_blank" rel="noreferrer">{{ $page->contact_cta_label }} <span aria-hidden="true">↗</span></a>
    </section>
</main>
