@php
    $slug = $page->slug;
    $slideCount = count($page->slides);
@endphp
<main id="main">
    <section class="island-hero {{ $slug }}-hero" aria-labelledby="{{ $slug }}-title">
        <img class="island-hero-image" src="{{ $page->imageUrl($page->hero_image) }}" alt="" aria-hidden="true">
        <div class="island-hero-shade"></div>
        <div class="island-hero-content">
            <span class="island-eyebrow">{{ $page->hero_eyebrow }}</span>
            <h1 id="{{ $slug }}-title">{{ $page->hero_headline }}<br><strong>{{ $page->hero_highlight }}</strong></h1>
            <p>{{ $page->hero_body }}</p>
            <a class="island-cta" href="#contact">{{ $page->hero_cta_label }} <span aria-hidden="true">↗</span></a>
        </div>
        <span class="island-hero-note">{{ $page->hero_note }}</span>
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

    <section class="destination-section {{ $slug }}-destinations" id="destinations" aria-labelledby="{{ $slug }}-destinations-title">
        <div class="island-section-heading">
            <span class="island-section-label">{{ $page->destinations_eyebrow }}</span>
            <h2 id="{{ $slug }}-destinations-title">{{ $page->destinations_headline }}</h2>
            <p>{{ $page->destinations_body }}</p>
        </div>
        <div class="destination-carousel" role="region" aria-roledescription="carousel" aria-label="{{ $page->name }} destinations">
            <div class="destination-viewport">
                <div class="destination-track">
                    @foreach ($page->slides as $slide)
                        <article class="destination-card" role="group" aria-roledescription="slide" aria-label="{{ $loop->iteration }} of {{ $slideCount }}">
                            <img src="{{ $page->imageUrl($slide['image']) }}" alt="{{ $slide['alt'] }}" loading="lazy">
                            <div><span>{{ $slide['eyebrow'] }}</span><h3>{{ $slide['title'] }}</h3><p>{{ $slide['body'] }}</p>@if (! empty($slide['link_url']) && ! empty($slide['link_label']))<a class="aceh-card-link" href="{{ $slide['link_url'] }}">{{ $slide['link_label'] }} <span aria-hidden="true">→</span></a>@endif</div>
                        </article>
                    @endforeach
                </div>
            </div>
            <div class="destination-controls">
                <p class="destination-count" aria-live="polite">1–{{ min(3, $slideCount) }} of {{ $slideCount }} destinations</p>
                <div class="destination-buttons">
                    <button class="destination-prev" type="button" aria-label="Previous destinations" disabled>←</button>
                    <button class="destination-next" type="button" aria-label="Next destinations">→</button>
                </div>
            </div>
        </div>
    </section>

    <section class="island-contact {{ $slug }}-contact" id="contact" aria-labelledby="{{ $slug }}-contact-title">
        <div>
            <span class="island-section-label">{{ $page->contact_eyebrow }}</span>
            <h2 id="{{ $slug }}-contact-title">{{ $page->contact_headline }}</h2>
            <p>{{ $page->contact_body }}</p>
        </div>
        <a class="island-cta" href="{{ $page->contact_href }}" target="_blank" rel="noreferrer">{{ $page->contact_cta_label }} <span aria-hidden="true">↗</span></a>
    </section>
</main>
