{{-- Full-width slideshow of our travellers on past China trips, with thumbnails underneath. Expects $about. --}}
@php
    $travellerPhotos = collect($about?->traveller_photos ?? [])
        ->filter(fn (array $photo): bool => filled($photo['image'] ?? null))
        ->values();
@endphp
@if ($travellerPhotos->isNotEmpty())
    <section class="china-moments" aria-labelledby="china-moments-title" data-moments>
        <div class="china-moments-heading">
            <div>
                <p class="china-moments-kicker"><span aria-hidden="true">★</span> Real trips · real travellers</p>
                <h2 id="china-moments-title">Moments from our China Muslim journeys</h2>
                <p>Photos from our recent group departures: mosque visits, halal meals and new friends from Lanzhou to Shanghai.</p>
            </div>
            <a class="china-moments-join" href="#china-series-title">Join the next departure <span aria-hidden="true">→</span></a>
        </div>

        <div class="china-moments-stage" role="region" aria-roledescription="carousel" aria-label="Traveller photos" tabindex="0" data-moments-stage>
            @foreach ($travellerPhotos as $photo)
                @php($photoUrl = $about->photoUrl($photo['image']))
                <figure @class(['china-moment', 'is-active' => $loop->first]) role="group" aria-roledescription="slide" aria-label="{{ $loop->iteration }} of {{ $travellerPhotos->count() }}" @unless ($loop->first) aria-hidden="true" @endunless data-moment>
                    <img class="china-moment-backdrop" src="{{ $photoUrl }}" alt="" aria-hidden="true" @unless ($loop->first) loading="lazy" @endunless>
                    <img class="china-moment-photo" src="{{ $photoUrl }}" alt="Smart Muslim travellers: {{ $photo['caption'] ?? 'group photo' }}" @unless ($loop->first) loading="lazy" @endunless>
                    <figcaption class="china-moment-caption">
                        <span class="china-moment-count">{{ sprintf('%02d', $loop->iteration) }} / {{ sprintf('%02d', $travellerPhotos->count()) }}</span>
                        <strong><span aria-hidden="true">📍</span> {{ $photo['caption'] ?? '' }}</strong>
                        <small>Smart Muslim group departure</small>
                    </figcaption>
                </figure>
            @endforeach

            <div class="china-moments-controls">
                <button type="button" class="china-moments-button" data-moments-prev aria-label="Previous photo">‹</button>
                <button type="button" class="china-moments-button" data-moments-play aria-label="Pause slideshow" aria-pressed="false">❚❚</button>
                <button type="button" class="china-moments-button" data-moments-next aria-label="Next photo">›</button>
                <button type="button" class="china-moments-button" data-moments-fullscreen aria-label="View full screen">⛶</button>
            </div>
            <div class="china-moments-progress" aria-hidden="true"><span data-moments-progress></span></div>
        </div>

        <div class="china-moments-thumbs" role="tablist" aria-label="Choose a photo" data-moments-thumbs>
            @foreach ($travellerPhotos as $photo)
                <button type="button" role="tab" @class(['china-moments-thumb', 'is-active' => $loop->first]) aria-selected="{{ $loop->first ? 'true' : 'false' }}" aria-label="Show photo {{ $loop->iteration }}: {{ $photo['caption'] ?? '' }}" data-moment-thumb="{{ $loop->index }}">
                    <img src="{{ $about->photoUrl($photo['image']) }}" alt="" loading="lazy">
                    <span>{{ $photo['caption'] ?? '' }}</span>
                </button>
            @endforeach
        </div>
    </section>
@endif
