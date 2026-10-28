@php
    $slug = $page->slug;
    $data = $page->package_data ?? [];
    $itineraries = $data['itineraries'] ?? [];
    $gallery = $data['gallery'] ?? [];

    // Without gallery photos the showcase falls back to the package's own hero and intro photos.
    if ($gallery === []) {
        $gallery = collect([[$page->hero_image, $page->hero_eyebrow], [$page->intro_image, $page->intro_eyebrow]])
            ->filter(fn (array $photo): bool => filled($photo[0]))
            ->unique(0)
            ->map(fn (array $photo): array => ['image' => $photo[0], 'alt' => $page->name, 'eyebrow' => (string) $photo[1], 'title' => $page->name])
            ->values()
            ->all();
    }

    $galleryCount = count($gallery);
    $hotels = $data['hotels'] ?? [];
    $packageBooking = new \App\Support\BookablePackage($page);
@endphp
<main id="{{ $seriesPageMainId ?? 'main' }}" class="package-page">
    <section class="island-hero indonesia-hero" aria-labelledby="{{ $slug }}-title">
        <img class="island-hero-image" src="{{ $page->imageUrl($page->hero_image) }}" alt="" aria-hidden="true">
        <div class="island-hero-shade"></div>
        <div class="island-hero-content">
            <span class="island-eyebrow">{{ $page->hero_eyebrow }}</span>
            <h1 id="{{ $slug }}-title">{{ $page->hero_headline }}<br><strong>{{ $page->hero_highlight }}</strong></h1>
            <p>{{ $page->hero_body }}</p>
            <div class="package-hero-actions">
                <a class="island-cta" href="#itineraries">{{ $page->hero_cta_label }} <span aria-hidden="true">↓</span></a>
                @include('partials.like-button', ['likeKey' => \App\Support\PackageLikes::key($page), 'likeLabel' => $page->hero_headline.' '.$page->hero_highlight, 'likeClass' => 'package-like--hero'])
            </div>
        </div>
        <span class="island-hero-note">{{ $page->hero_note }}</span>
    </section>

    <section class="island-intro" aria-labelledby="{{ $slug }}-intro-title">
        <div class="island-intro-copy">
            <span class="island-section-label">{{ $page->intro_eyebrow }}</span>
            <h2 id="{{ $slug }}-intro-title">{{ $page->intro_headline }} <span>{{ $page->intro_highlight }}</span></h2>
            <p>{{ $page->intro_body }}</p>
            <a class="island-text-link" href="#itineraries">{{ $page->intro_link_label }} <span aria-hidden="true">→</span></a>
        </div>
        <div class="island-photo-stack indonesia-intro-photo">
            <img src="{{ $page->imageUrl($page->intro_image) }}" alt="{{ $page->intro_image_alt }}" loading="lazy" decoding="async">
            <span class="island-photo-caption">{{ $page->intro_image_caption }}</span>
        </div>
    </section>

    <section class="island-highlights indonesia-highlights" aria-labelledby="{{ $slug }}-highlights-title">
        <div class="island-section-heading">
            <span class="island-section-label">{{ $page->highlights_eyebrow }}</span>
            <h2 id="{{ $slug }}-highlights-title">{{ $page->highlights_headline }}</h2>
            <p>{{ $page->highlights_body }}</p>
        </div>
        <div class="island-highlight-grid indonesia-highlight-grid">
            @foreach ($page->highlights as $highlight)
                <article class="island-highlight">
                    <span class="island-highlight-number">{{ sprintf('%02d', $loop->iteration) }}</span>
                    <h3>{{ $highlight['title'] }}</h3>
                    <p>{{ $highlight['body'] }}</p>
                </article>
            @endforeach
        </div>
    </section>

    <section class="aceh-itineraries bali-itineraries" id="itineraries" aria-labelledby="{{ $slug }}-itineraries-title">
        <div class="island-section-heading">
            <span class="island-section-label">{{ $data['itineraries_eyebrow'] ?? '' }}</span>
            <h2 id="{{ $slug }}-itineraries-title">{{ $data['itineraries_headline'] ?? '' }}</h2>
            <p>{{ $data['itineraries_body'] ?? '' }}</p>
        </div>

        <div class="aceh-itinerary-grid bali-itinerary-grid">
            @foreach ($itineraries as $itinerary)
                <article class="aceh-itinerary-card{{ ! empty($itinerary['featured']) ? ' aceh-itinerary-card-featured' : '' }}">
                    <header class="aceh-itinerary-heading">
                        <span>{{ $itinerary['duration'] }}</span>
                        <h3>{{ $itinerary['title'] }}</h3>
                        <p>{{ $itinerary['summary'] }}</p>
                    </header>
                    @if (($data['template'] ?? 'standard') !== 'compact')
                    <ol class="aceh-day-list">
                        @foreach ($itinerary['days'] as $day)
                            <li class="aceh-day"><span class="aceh-day-number">D{{ $loop->iteration }}</span><div><h4>{{ $day['title'] }}</h4><p>{{ $day['body'] }}</p>@if (! empty($day['meals']))<span class="aceh-meals">{{ $day['meals'] }}</span>@endif</div></li>
                        @endforeach
                    </ol>
                    @endif
                    <div class="aceh-itinerary-actions">
                        @if ($itineraryBookingUrl = $packageBooking->bookNowUrl(option: 'itinerary-'.$loop->index))
                            <a class="package-book-now" href="{{ $itineraryBookingUrl }}">Book now <span aria-hidden="true">→</span></a>
                        @endif
                        <a class="aceh-itinerary-cta" href="{{ $itinerary['cta_url'] }}" target="_blank" rel="noreferrer">{{ $itinerary['cta_label'] }} <span aria-hidden="true">↗</span></a>
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    @if ($galleryCount > 0)
        {{-- Full-screen photo showcase: the heading sits over the photos, thumbnails with a progress bar choose the photo. --}}
        <section class="aceh-gallery-section bali-gallery-section pk-showcase" aria-labelledby="{{ $slug }}-gallery-title">
            <div class="bali-gallery pk-showcase-stage" role="region" aria-roledescription="carousel" aria-label="{{ $data['gallery_headline'] ?? $page->name }}" tabindex="0">
                <div class="bali-slides">
                    @foreach ($gallery as $slide)
                        <figure class="bali-slide{{ $loop->first ? ' is-active' : '' }}" aria-roledescription="slide" aria-label="{{ $loop->iteration }} of {{ $galleryCount }}" aria-hidden="{{ $loop->first ? 'false' : 'true' }}" @unless ($loop->first) inert @endunless>
                            <img src="{{ $page->imageUrl($slide['image']) }}" alt="{{ $slide['alt'] }}" @unless ($loop->first) loading="lazy" @endunless decoding="async">
                            <figcaption>
                                @if ($galleryCount > 1)
                                    <em aria-hidden="true">{{ sprintf('%02d', $loop->iteration) }}</em>
                                @endif
                                <span>{{ $slide['eyebrow'] }}</span><strong>{{ $slide['title'] }}</strong>
                            </figcaption>
                        </figure>
                    @endforeach
                </div>

                <header class="pk-showcase-heading">
                    @if (filled($data['gallery_eyebrow'] ?? null))
                        <span class="pk-showcase-eyebrow">{{ $data['gallery_eyebrow'] }}</span>
                    @endif
                    <h2 id="{{ $slug }}-gallery-title">{{ $data['gallery_headline'] ?? $page->name }}</h2>
                    @if (filled($data['gallery_body'] ?? null))
                        <p>{{ $data['gallery_body'] }}</p>
                    @endif
                </header>

                <button class="pk-showcase-fullscreen" type="button" data-pk-fullscreen aria-label="View full screen"><span aria-hidden="true">⛶</span></button>

                @if ($galleryCount > 1)
                    <button class="bali-carousel-arrow bali-carousel-prev" type="button" aria-label="Previous photo"><span aria-hidden="true">←</span></button>
                    <button class="bali-carousel-arrow bali-carousel-next" type="button" aria-label="Next photo"><span aria-hidden="true">→</span></button>
                    <div class="bali-carousel-toolbar">
                        <button class="bali-carousel-toggle" type="button" aria-label="Pause slideshow" aria-pressed="false"><span aria-hidden="true">Ⅱ</span></button>
                        <div class="bali-slide-dots pk-showcase-thumbs" role="group" aria-label="Choose a photo">
                            @foreach ($gallery as $slide)
                                <button type="button" @if ($loop->first) class="is-active" aria-current="true" @endif aria-label="Show photo {{ $loop->iteration }}: {{ $slide['title'] }}">
                                    <img src="{{ $page->imageUrl($slide['image']) }}" alt="" loading="lazy" decoding="async">
                                    <span>{{ $slide['title'] }}</span>
                                    <i class="pk-showcase-progress" aria-hidden="true"></i>
                                </button>
                            @endforeach
                        </div>
                        <span class="bali-slide-count" aria-live="off"><strong>01</strong><span>/ {{ sprintf('%02d', $galleryCount) }}</span></span>
                    </div>
                @endif
            </div>
        </section>
    @endif

    <section class="aceh-package-details bali-package-details" aria-labelledby="{{ $slug }}-details-title">
        <div class="island-section-heading">
            <span class="island-section-label">{{ $data['details_eyebrow'] ?? '' }}</span>
            <h2 id="{{ $slug }}-details-title">{{ $data['details_headline'] ?? '' }}</h2>
            <p>{{ $data['details_body'] ?? '' }}</p>
        </div>
        <div class="aceh-details-grid">
            <article class="aceh-details-card">
                <span class="aceh-details-kicker">PART OF THE PACKAGE</span>
                <h3>Price includes</h3>
                <ul class="aceh-inclusion-list">
                    @foreach ($data['inclusions'] ?? [] as $item)
                        <li>{{ $item }}</li>
                    @endforeach
                </ul>
            </article>
            <article class="aceh-details-card aceh-exclusions-card">
                <span class="aceh-details-kicker">PLAN FOR SEPARATELY</span>
                <h3>Price does not include</h3>
                <ul class="aceh-exclusion-list">
                    @foreach ($data['exclusions'] ?? [] as $item)
                        <li>{{ $item }}</li>
                    @endforeach
                </ul>
            </article>
        </div>
    </section>

    @if ($hotels !== [])
        <section class="aceh-accommodation bali-accommodation" aria-labelledby="{{ $slug }}-accommodation-title">
            <div class="island-section-heading">
                <span class="island-section-label">{{ $data['accommodation_eyebrow'] ?? '' }}</span>
                <h2 id="{{ $slug }}-accommodation-title">{{ $data['accommodation_headline'] ?? '' }}</h2>
                <p>{{ $data['accommodation_body'] ?? '' }}</p>
            </div>
            <div class="aceh-hotel-grid">
                @foreach ($hotels as $hotel)
                    <article class="aceh-hotel-card">
                        <span class="aceh-hotel-label">{{ $hotel['label'] }}</span>
                        <h3>{{ $hotel['name'] }}</h3>
                        <address>{{ $hotel['address'] }}</address>
                        @if (! empty($hotel['phone']))
                            <a href="tel:{{ preg_replace('/[^+\d]/', '', $hotel['phone']) }}">{{ $hotel['phone'] }} <span aria-hidden="true">↗</span></a>
                        @endif
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    <section class="island-contact aceh-contact bali-contact" id="contact" aria-labelledby="{{ $slug }}-contact-title">
        <div>
            <span class="island-section-label">{{ $page->contact_eyebrow }}</span>
            <h2 id="{{ $slug }}-contact-title">{{ $page->contact_headline }}</h2>
            <p>{{ $page->contact_body }}</p>
        </div>
        <a class="island-cta" href="{{ $page->contact_href }}" target="_blank" rel="noreferrer">{{ $page->contact_cta_label }} <span aria-hidden="true">↗</span></a>
    </section>
</main>
