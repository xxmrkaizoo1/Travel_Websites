<section class="series-section" id="destinations" aria-labelledby="series-title">
    <div class="island-section-heading">
        <span class="island-section-label">{{ $page->destinations_eyebrow }}</span>
        <h2 id="series-title">{{ $page->destinations_headline }}</h2>
        <p>{{ $page->destinations_body }}</p>
    </div>

    <nav class="series-tabs" aria-label="Series destinations">
        @foreach ($series as $destination)
            <a class="series-tab" href="#series-{{ \Illuminate\Support\Str::slug($destination['name']) }}">
                <span>{{ sprintf('%02d', $loop->iteration) }}</span>{{ $destination['name'] }}
            </a>
        @endforeach
    </nav>

    @foreach ($series as $destination)
        <article class="series-destination{{ $loop->even ? ' is-flipped' : '' }}" id="series-{{ \Illuminate\Support\Str::slug($destination['name']) }}" aria-labelledby="series-{{ \Illuminate\Support\Str::slug($destination['name']) }}-title">
            <header class="series-head">
                <span class="series-number" aria-hidden="true">{{ sprintf('%02d', $loop->iteration) }}</span>
                <div>
                    <span class="series-country">{{ $destination['country'] }}</span>
                    <h3 id="series-{{ \Illuminate\Support\Str::slug($destination['name']) }}-title">{{ $destination['name'] }}</h3>
                    <p>{{ $destination['tagline'] }}</p>
                </div>
            </header>

            <div class="series-body">
                <div class="series-copy">
                    <p class="series-lead">{{ $destination['intro'] }}</p>
                    <div class="series-advantages">
                        @foreach ($destination['advantages'] as $advantage)
                            <div class="series-advantage">
                                <span class="series-advantage-icon" aria-hidden="true">{{ ($advantage['icon'] ?? '') !== '' ? $advantage['icon'] : '✓' }}</span>
                                <div>
                                    <h4>{{ $advantage['title'] }}</h4>
                                    <p>{{ $advantage['body'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @if (! empty($destination['flag']))
                <figure class="series-flag">
                    <img src="{{ $page->imageUrl($destination['flag']) }}" alt="Flag of {{ $destination['country'] }}" loading="lazy">
                    <figcaption><strong>{{ $destination['name'] }}</strong>{{ $destination['country'] }}</figcaption>
                </figure>
                @endif
            </div>

            <div class="series-gallery">
                @foreach ($destination['gallery'] as $photo)
                    <figure class="series-photo">
                        <img src="{{ $page->imageUrl($photo['image']) }}" alt="{{ $photo['alt'] }}" loading="lazy">
                        <figcaption>{{ $photo['caption'] }}</figcaption>
                    </figure>
                @endforeach
            </div>
        </article>
    @endforeach
</section>
