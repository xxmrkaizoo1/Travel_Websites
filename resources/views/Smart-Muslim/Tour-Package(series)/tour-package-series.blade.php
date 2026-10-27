@php
    $seriesBooking = new \App\Support\BookablePackage($page);
@endphp
<section class="series-section" id="destinations" aria-labelledby="series-title">
    <div class="island-section-heading">
        <span class="island-section-label">{{ $page->destinations_eyebrow }}</span>
        <h2 id="series-title">{{ $page->destinations_headline }}</h2>
        <p>{{ $page->destinations_body }}</p>
    </div>

    <nav class="series-tabs" aria-label="Choose a destination">
        @foreach ($series as $destination)
            <a class="series-tab" href="#series-{{ \Illuminate\Support\Str::slug($destination['name']) }}" @if ($loop->first) aria-current="true" @endif>
                <span>{{ sprintf('%02d', $loop->iteration) }}</span>{{ $destination['name'] }}
            </a>
        @endforeach
    </nav>

    <div class="series-carousel" role="region" aria-roledescription="carousel" aria-label="2026 tour destinations">
        <div class="series-viewport" tabindex="0">
            <div class="series-track">
                @foreach ($series as $destination)
                    <article class="series-destination{{ $loop->even ? ' is-flipped' : '' }}" id="series-{{ \Illuminate\Support\Str::slug($destination['name']) }}" role="group" aria-roledescription="slide" aria-label="{{ $loop->iteration }} of {{ count($series) }}" aria-labelledby="series-{{ \Illuminate\Support\Str::slug($destination['name']) }}-title">
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
                            <figure class="series-flag">
                                @if (! empty($destination['flag']))
                                    <img src="{{ $page->imageUrl($destination['flag']) }}" alt="Flag of {{ $destination['country'] }}" loading="lazy">
                                @else
                                    <img src="{{ $page->imageUrl(\App\Models\TourPackagePage::PLACEHOLDER_IMAGE) }}" alt="" loading="lazy">
                                @endif
                                <figcaption><strong>{{ $destination['name'] }}</strong>{{ $destination['country'] }}</figcaption>
                            </figure>
                        </div>

                        <div class="series-gallery">
                            @forelse ($destination['gallery'] ?? [] as $photo)
                                <figure class="series-photo">
                                    <img src="{{ $page->imageUrl($photo['image']) }}" alt="{{ $photo['alt'] }}" loading="lazy">
                                    <figcaption>{{ $photo['caption'] }}</figcaption>
                                </figure>
                            @empty
                                @for ($placeholder = 0; $placeholder < \App\Models\TourPackagePage::PLACEHOLDER_GALLERY_SIZE; $placeholder++)
                                    <figure class="series-photo series-photo-placeholder">
                                        <img src="{{ $page->imageUrl(\App\Models\TourPackagePage::PLACEHOLDER_IMAGE) }}" alt="" loading="lazy">
                                        <figcaption>Photo coming soon</figcaption>
                                    </figure>
                                @endfor
                            @endforelse
                        </div>

                        <div class="series-destination-actions">
                            <button class="series-details-open" type="button" data-series-details-open aria-haspopup="dialog" aria-controls="{{ \Illuminate\Support\Str::slug($destination['name']) }}-package-details">
                                Explore the full {{ $destination['name'] }} package <span aria-hidden="true">↗</span>
                            </button>
                            @if ($destinationBookingUrl = $seriesBooking->bookNowUrl($destination['name']))
                                <a class="package-book-now" href="{{ $destinationBookingUrl }}">Book now <span aria-hidden="true">→</span></a>
                            @endif
                            @include('partials.like-button', ['likeKey' => \App\Support\PackageLikes::key($page, $destination['name']), 'likeLabel' => $destination['name']])
                        </div>
                    </article>
                @endforeach
            </div>
        </div>

        <div class="series-carousel-controls">
            <p class="series-count" aria-live="polite">Destination 1 of {{ count($series) }}</p>
            <div class="series-control-buttons" role="group" aria-label="Destination slide controls">
                <button class="series-prev" type="button" aria-label="Previous destination" disabled>←</button>
                <button class="series-next" type="button" aria-label="Next destination">→</button>
            </div>
        </div>
    </div>

    @php
        $seoul = collect($series)->firstWhere('name', 'Seoul');
        $seoulDetails = $seoul['package_details'] ?? [];
        $seoulItinerarySlides = array_chunk($seoulDetails['itinerary'] ?? [], 3);
        $seoulHotels = $seoulDetails['hotels'] ?? [];

        if ($seoulHotels === [] && ! empty($seoulDetails['hotel']['name'])) {
            $seoulHotels = [[
                'name' => $seoulDetails['hotel']['name'],
                'room' => $seoulDetails['hotel']['room'] ?? '',
                'location' => $seoulDetails['hotel']['location'] ?? '',
                'note' => $seoulDetails['hotel']['note'] ?? '',
            ]];
        }
    @endphp
    @if ($seoul !== null && $seoulDetails !== [])
    <dialog class="series-details-dialog" id="seoul-package-details" aria-labelledby="seoul-package-details-title">
        <div class="series-details-panel">
            <header class="series-details-header">
                <div>
                    <span class="island-section-label">{{ $seoulDetails['duration'] }} · {{ $seoulDetails['outbound']['flight'] }}</span>
                    <h2 id="seoul-package-details-title">{{ $seoulDetails['headline'] }}</h2>
                </div>
                <button class="series-details-close" type="button" data-series-details-close aria-label="Close package details">×</button>
            </header>

            <div class="series-details-viewport">
                <div class="series-details-track">
                    <section class="series-details-slide" aria-label="Seoul package overview">
                        <p class="series-details-kicker">YOUR GROUP DEPARTURE</p>
                        <h3>{{ $seoulDetails['headline'] }}</h3>
                        <div class="series-details-highlights">
                            <article><span>Travel dates</span><strong>{{ $seoulDetails['travel_dates'] }}</strong></article>
                            <article><span>Package price</span><strong>{{ $seoulDetails['price_from'] }}</strong></article>
                            <article><span>Tour code</span><strong>{{ $seoulDetails['tour_code'] }}</strong></article>
                            <article><span>Duration</span><strong>{{ $seoulDetails['duration'] }}</strong></article>
                        </div>
                        @include('Smart-Muslim.Tour-Package(series).partials.departures', ['departures' => $seoulDetails['departures'] ?? [], 'bookingUrl' => $seriesBooking->bookNowUrl('Seoul')])
                        <p class="series-details-note">{{ $seoulDetails['note'] }}</p>
                    </section>

                    @foreach ($seoulItinerarySlides as $days)
                        <section class="series-details-slide" aria-label="Seoul itinerary, {{ $days[0]['day'] }} to {{ $days[count($days) - 1]['day'] }}">
                            <p class="series-details-kicker">THE ITINERARY · {{ $days[0]['day'] }}–{{ $days[count($days) - 1]['day'] }}</p>
                            <h3>{{ $seoulDetails['headline'] }}</h3>
                            <div class="series-itinerary">
                                @foreach ($days as $day)
                                    @include('Smart-Muslim.Tour-Package(series).partials.itinerary-day', ['day' => $day])
                                @endforeach
                            </div>
                            @if ($loop->last && ($seoulDetails['itinerary_note'] ?? '') !== '')
                                <p class="series-details-note">{{ $seoulDetails['itinerary_note'] }}</p>
                            @endif
                        </section>
                    @endforeach

                    <section class="series-details-slide" aria-label="Seoul package inclusions and exclusions">
                        <p class="series-details-kicker">WHAT'S INCLUDED</p>
                        <h3>Know what's covered.</h3>
                        <div class="series-details-columns">
                            <div class="series-coverage series-coverage--included"><h4><span aria-hidden="true">✓</span> Package includes</h4><ul>@foreach ($seoulDetails['includes'] as $item)<li>{{ $item }}</li>@endforeach</ul></div>
                            <div class="series-coverage series-coverage--excluded"><h4><span aria-hidden="true">✕</span> Not included</h4><ul>@foreach ($seoulDetails['excludes'] as $item)<li>{{ $item }}</li>@endforeach</ul></div>
                        </div>
                    </section>

                    <section class="series-details-slide" aria-label="Seoul flights and hotel">
                        <p class="series-details-kicker">FLIGHTS &amp; STAY</p>
                        <h3>Your travel arrangements.</h3>
                        @include('Smart-Muslim.Tour-Package(series).partials.travel-arrangements', ['details' => $seoulDetails, 'hotels' => $seoulHotels, 'hotelsHeading' => 'Hotels · twin-share'])
                    </section>

                    <section class="series-details-slide" aria-label="Seoul travel essentials and seasonal weather">
                        <p class="series-details-kicker">BEFORE YOU GO</p>
                        <h3>Travel essentials &amp; weather.</h3>
                        @include('Smart-Muslim.Tour-Package(series).partials.essentials-weather', ['essentials' => $seoulDetails['essentials'] ?? [], 'weather' => $seoulDetails['weather'] ?? []])
                    </section>
                </div>
            </div>

            <footer class="series-details-footer">
                <p class="series-details-count" aria-live="polite">Step 1 of {{ 4 + count($seoulItinerarySlides) }}</p>
                <div class="series-control-buttons" role="group" aria-label="Package detail slide controls">
                    <button class="series-details-prev" type="button" aria-label="Previous package details" disabled>←</button>
                    <button class="series-details-next" type="button" aria-label="Next package details">→</button>
                </div>
            </footer>
        </div>
    </dialog>
    @endif

    @php
        $kunming = collect($series)->firstWhere('name', 'Kunming');
        $kunmingDetails = $kunming['package_details'] ?? [];
        $kunmingItinerarySlides = array_chunk($kunmingDetails['itinerary'] ?? [], 3);
    @endphp
    @if ($kunming !== null && $kunmingDetails !== [])
    <dialog class="series-details-dialog" id="kunming-package-details" aria-labelledby="kunming-package-details-title">
        <div class="series-details-panel">
            <header class="series-details-header">
                <div>
                    <span class="island-section-label">{{ $kunmingDetails['duration'] }} · {{ $kunmingDetails['outbound']['flight'] }}</span>
                    <h2 id="kunming-package-details-title">{{ $kunmingDetails['headline'] }}</h2>
                </div>
                <button class="series-details-close" type="button" data-series-details-close aria-label="Close Kunming package details">×</button>
            </header>

            <div class="series-details-viewport">
                <div class="series-details-track">
                    <section class="series-details-slide" aria-label="Kunming package overview">
                        <p class="series-details-kicker">YOUR GROUP DEPARTURE</p>
                        <h3>{{ $kunmingDetails['headline'] }}</h3>
                        <div class="series-details-highlights">
                            <article><span>Travel dates</span><strong>{{ $kunmingDetails['travel_dates'] }}</strong></article>
                            <article><span>Package price</span><strong>{{ $kunmingDetails['price_from'] }}</strong></article>
                            <article><span>Tour code</span><strong>{{ $kunmingDetails['tour_code'] }}</strong></article>
                            <article><span>Duration</span><strong>{{ $kunmingDetails['duration'] }}</strong></article>
                        </div>
                        @include('Smart-Muslim.Tour-Package(series).partials.departures', ['departures' => $kunmingDetails['departures'] ?? [], 'bookingUrl' => $seriesBooking->bookNowUrl('Kunming')])
                        <p class="series-details-note">{{ $kunmingDetails['note'] }}</p>
                    </section>

                    @foreach ($kunmingItinerarySlides as $days)
                        <section class="series-details-slide" aria-label="Kunming itinerary, {{ $days[0]['day'] }} to {{ $days[count($days) - 1]['day'] }}">
                            <p class="series-details-kicker">THE ITINERARY · {{ $days[0]['day'] }}–{{ $days[count($days) - 1]['day'] }}</p>
                            <h3>{{ $loop->first ? 'From Kunming to Dali.' : ($loop->last ? 'Return to Kunming.' : 'Highlands, old towns & mountain views.') }}</h3>
                            <div class="series-itinerary">
                                @foreach ($days as $day)
                                    @include('Smart-Muslim.Tour-Package(series).partials.itinerary-day', ['day' => $day])
                                @endforeach
                            </div>
                            @if ($loop->last && ($kunmingDetails['itinerary_note'] ?? '') !== '')
                                <p class="series-details-note">{{ $kunmingDetails['itinerary_note'] }}</p>
                            @endif
                        </section>
                    @endforeach

                    <section class="series-details-slide" aria-label="Kunming package inclusions and exclusions">
                        <p class="series-details-kicker">WHAT'S INCLUDED</p>
                        <h3>Know what's covered.</h3>
                        <div class="series-details-columns">
                            <div class="series-coverage series-coverage--included"><h4><span aria-hidden="true">✓</span> Package includes</h4><ul>@foreach ($kunmingDetails['includes'] as $item)<li>{{ $item }}</li>@endforeach</ul></div>
                            <div class="series-coverage series-coverage--excluded"><h4><span aria-hidden="true">✕</span> Not included</h4><ul>@foreach ($kunmingDetails['excludes'] as $item)<li>{{ $item }}</li>@endforeach</ul></div>
                        </div>
                    </section>

                    <section class="series-details-slide" aria-label="Kunming flights and hotels">
                        <p class="series-details-kicker">FLIGHTS &amp; STAYS</p>
                        <h3>Your travel arrangements.</h3>
                        @include('Smart-Muslim.Tour-Package(series).partials.travel-arrangements', ['details' => $kunmingDetails, 'hotels' => $kunmingDetails['hotels'] ?? [], 'hotelsHeading' => 'Hotels · twin-share'])
                    </section>

                    <section class="series-details-slide" aria-label="Kunming travel essentials and seasonal weather">
                        <p class="series-details-kicker">BEFORE YOU GO</p>
                        <h3>Travel essentials &amp; weather.</h3>
                        @include('Smart-Muslim.Tour-Package(series).partials.essentials-weather', ['essentials' => $kunmingDetails['essentials'] ?? [], 'weather' => $kunmingDetails['weather'] ?? []])
                    </section>
                </div>
            </div>

            <footer class="series-details-footer">
                <p class="series-details-count" aria-live="polite">Step 1 of {{ 4 + count($kunmingItinerarySlides) }}</p>
                <div class="series-control-buttons" role="group" aria-label="Kunming package detail slide controls">
                    <button class="series-details-prev" type="button" aria-label="Previous package details" disabled>←</button>
                    <button class="series-details-next" type="button" aria-label="Next package details">→</button>
                </div>
            </footer>
        </div>
    </dialog>
    @endif

    @php
        $daNang = collect($series)->firstWhere('name', 'Da Nang');
        $daNangDetails = $daNang['package_details'] ?? [];
        $daNangItinerarySlides = array_chunk($daNangDetails['itinerary'] ?? [], 3);
        $daNangHotels = $daNangDetails['hotels'] ?? [];

        if ($daNangHotels === [] && ! empty($daNangDetails['hotel']['name'])) {
            $daNangHotels = [[
                'name' => $daNangDetails['hotel']['name'],
                'room' => $daNangDetails['hotel']['room'] ?? '',
                'location' => $daNangDetails['hotel']['location'] ?? '',
                'note' => $daNangDetails['hotel']['note'] ?? '',
            ]];
        }
    @endphp
    @if ($daNang !== null && $daNangDetails !== [])
    <dialog class="series-details-dialog" id="da-nang-package-details" aria-labelledby="da-nang-package-details-title">
        <div class="series-details-panel">
            <header class="series-details-header">
                <div>
                    <span class="island-section-label">{{ $daNangDetails['duration'] }} · {{ $daNangDetails['outbound']['flight'] }}</span>
                    <h2 id="da-nang-package-details-title">{{ $daNangDetails['headline'] }}</h2>
                </div>
                <button class="series-details-close" type="button" data-series-details-close aria-label="Close Da Nang package details">×</button>
            </header>

            <div class="series-details-viewport">
                <div class="series-details-track">
                    <section class="series-details-slide" aria-label="Da Nang package overview">
                        <p class="series-details-kicker">YOUR GROUP DEPARTURE</p>
                        <h3>{{ $daNangDetails['headline'] }}</h3>
                        <div class="series-details-highlights">
                            <article><span>Travel dates</span><strong>{{ $daNangDetails['travel_dates'] }}</strong></article>
                            <article><span>Package price</span><strong>{{ $daNangDetails['price_from'] }}</strong></article>
                            <article><span>Tour code</span><strong>{{ $daNangDetails['tour_code'] }}</strong></article>
                            <article><span>Duration</span><strong>{{ $daNangDetails['duration'] }}</strong></article>
                        </div>
                        @include('Smart-Muslim.Tour-Package(series).partials.departures', ['departures' => $daNangDetails['departures'] ?? [], 'bookingUrl' => $seriesBooking->bookNowUrl('Da Nang')])
                        <p class="series-details-note">{{ $daNangDetails['note'] }}</p>
                    </section>

                    @foreach ($daNangItinerarySlides as $days)
                        <section class="series-details-slide" aria-label="Da Nang itinerary, {{ $days[0]['day'] }} to {{ $days[count($days) - 1]['day'] }}">
                            <p class="series-details-kicker">THE ITINERARY · {{ $days[0]['day'] }}–{{ $days[count($days) - 1]['day'] }}</p>
                            <h3>{{ $daNangDetails['headline'] }}</h3>
                            <div class="series-itinerary">
                                @foreach ($days as $day)
                                    @include('Smart-Muslim.Tour-Package(series).partials.itinerary-day', ['day' => $day])
                                @endforeach
                            </div>
                            @if ($loop->last && ($daNangDetails['itinerary_note'] ?? '') !== '')
                                <p class="series-details-note">{{ $daNangDetails['itinerary_note'] }}</p>
                            @endif
                        </section>
                    @endforeach

                    <section class="series-details-slide" aria-label="Da Nang package inclusions and exclusions">
                        <p class="series-details-kicker">WHAT'S INCLUDED</p>
                        <h3>Know what's covered.</h3>
                        <div class="series-details-columns">
                            <div class="series-coverage series-coverage--included"><h4><span aria-hidden="true">✓</span> Package includes</h4><ul>@foreach ($daNangDetails['includes'] as $item)<li>{{ $item }}</li>@endforeach</ul></div>
                            <div class="series-coverage series-coverage--excluded"><h4><span aria-hidden="true">✕</span> Not included</h4><ul>@foreach ($daNangDetails['excludes'] as $item)<li>{{ $item }}</li>@endforeach</ul></div>
                        </div>
                    </section>

                    <section class="series-details-slide" aria-label="Da Nang flights and hotel">
                        <p class="series-details-kicker">FLIGHTS &amp; STAY</p>
                        <h3>Your travel arrangements.</h3>
                        @include('Smart-Muslim.Tour-Package(series).partials.travel-arrangements', ['details' => $daNangDetails, 'hotels' => $daNangHotels, 'hotelsHeading' => 'Hotel · twin-share'])
                    </section>

                    <section class="series-details-slide" aria-label="Da Nang travel essentials and seasonal weather">
                        <p class="series-details-kicker">BEFORE YOU GO</p>
                        <h3>Travel essentials &amp; weather.</h3>
                        @include('Smart-Muslim.Tour-Package(series).partials.essentials-weather', ['essentials' => $daNangDetails['essentials'] ?? [], 'weather' => $daNangDetails['weather'] ?? []])
                    </section>
                </div>
            </div>

            <footer class="series-details-footer">
                <p class="series-details-count" aria-live="polite">Step 1 of {{ 4 + count($daNangItinerarySlides) }}</p>
                <div class="series-control-buttons" role="group" aria-label="Da Nang package detail slide controls">
                    <button class="series-details-prev" type="button" aria-label="Previous package details" disabled>←</button>
                    <button class="series-details-next" type="button" aria-label="Next package details">→</button>
                </div>
            </footer>
        </div>
    </dialog>
    @endif

    @foreach ($series as $destination)
        @continue(in_array($destination['name'], ['Seoul', 'Kunming', 'Da Nang'], true))

        @php
            $destinationSlug = \Illuminate\Support\Str::slug($destination['name']);
            $destinationPackageDetails = $destination['package_details'] ?? [];
            $destinationItinerarySlides = array_chunk($destinationPackageDetails['itinerary'] ?? [], 3);
            $hasPackageCoverage = ! empty($destinationPackageDetails['includes']) || ! empty($destinationPackageDetails['excludes']);
            $hasTravelArrangements = filled($destinationPackageDetails['outbound']['flight'] ?? '')
                || filled($destinationPackageDetails['return']['flight'] ?? '')
                || ! empty($destinationPackageDetails['hotels']);
            $destinationEssentials = array_filter($destinationPackageDetails['essentials'] ?? []);
            $destinationWeather = $destinationPackageDetails['weather'] ?? [];
            $hasPreTravelDetails = $destinationEssentials !== [] || $destinationWeather !== [];
            $destinationDetailsSlideCount = 2
                + (empty($destination['gallery']) ? 0 : 1)
                + count($destinationItinerarySlides)
                + ($hasPackageCoverage ? 1 : 0)
                + ($hasTravelArrangements ? 1 : 0)
                + ($hasPreTravelDetails ? 1 : 0);
        @endphp
        <dialog class="series-details-dialog" id="{{ $destinationSlug }}-package-details" aria-labelledby="{{ $destinationSlug }}-package-details-title">
            <div class="series-details-panel">
                <header class="series-details-header">
                    <div>
                        <span class="island-section-label">{{ $destination['country'] }}</span>
                        <h2 id="{{ $destinationSlug }}-package-details-title">{{ $destination['name'] }} tour package</h2>
                    </div>
                    <button class="series-details-close" type="button" data-series-details-close aria-label="Close {{ $destination['name'] }} package details">×</button>
                </header>

                <div class="series-details-viewport">
                    <div class="series-details-track">
                        <section class="series-details-slide" aria-label="{{ $destination['name'] }} destination overview">
                            <p class="series-details-kicker">{{ filled($destinationPackageDetails['headline'] ?? '') ? 'PACKAGE OVERVIEW' : 'DESTINATION OVERVIEW' }}</p>
                            <h3>{{ $destinationPackageDetails['headline'] ?? $destination['tagline'] }}</h3>
                            @if (filled($destinationPackageDetails['headline'] ?? '') && $destination['tagline'] !== '')
                                <p class="series-details-lead">{{ $destination['tagline'] }}</p>
                            @endif
                            <p class="series-details-note">{{ $destination['intro'] }}</p>
                            <div class="series-details-generic-highlights">
                                @foreach ($destination['advantages'] as $advantage)
                                    <article>
                                        <span aria-hidden="true">{{ ($advantage['icon'] ?? '') !== '' ? $advantage['icon'] : '✓' }}</span>
                                        <div><h4>{{ $advantage['title'] }}</h4><p>{{ $advantage['body'] }}</p></div>
                                    </article>
                                @endforeach
                            </div>
                            @if ($destinationPackageDetails !== [])
                                <div class="series-details-highlights">
                                    @foreach (['travel_dates' => 'Travel dates', 'price_from' => 'Package price', 'tour_code' => 'Tour code', 'duration' => 'Duration'] as $key => $label)
                                        @if (filled($destinationPackageDetails[$key] ?? ''))
                                            <article><span>{{ $label }}</span><strong>{{ $destinationPackageDetails[$key] }}</strong></article>
                                        @endif
                                    @endforeach
                                </div>
                                @if (! empty($destinationPackageDetails['departures']))
                                    @include('Smart-Muslim.Tour-Package(series).partials.departures', ['departures' => $destinationPackageDetails['departures'] ?? [], 'bookingUrl' => $seriesBooking->bookNowUrl($destination['name'])])
                                @endif
                                @if (filled($destinationPackageDetails['note'] ?? ''))
                                    <p class="series-details-note">{{ $destinationPackageDetails['note'] }}</p>
                                @endif
                            @endif
                        </section>

                        @foreach ($destinationItinerarySlides as $days)
                            <section class="series-details-slide" aria-label="{{ $destination['name'] }} itinerary, {{ $days[0]['day'] }} to {{ $days[count($days) - 1]['day'] }}">
                                <p class="series-details-kicker">THE ITINERARY · {{ $days[0]['day'] }}–{{ $days[count($days) - 1]['day'] }}</p>
                                <h3>{{ $destinationPackageDetails['headline'] ?? $destination['name'].' itinerary' }}</h3>
                                <div class="series-itinerary">
                                    @foreach ($days as $day)
                                        @include('Smart-Muslim.Tour-Package(series).partials.itinerary-day', ['day' => $day])
                                    @endforeach
                                </div>
                                @if ($loop->last && filled($destinationPackageDetails['itinerary_note'] ?? ''))
                                    <p class="series-details-note">{{ $destinationPackageDetails['itinerary_note'] }}</p>
                                @endif
                            </section>
                        @endforeach

                        @if ($hasPackageCoverage)
                            <section class="series-details-slide" aria-label="{{ $destination['name'] }} package inclusions and exclusions">
                                <p class="series-details-kicker">WHAT'S INCLUDED</p>
                                <h3>Know what's covered.</h3>
                                <div class="series-details-columns">
                                    @if (! empty($destinationPackageDetails['includes']))
                                        <div class="series-coverage series-coverage--included"><h4><span aria-hidden="true">✓</span> Package includes</h4><ul>@foreach ($destinationPackageDetails['includes'] as $item)<li>{{ $item }}</li>@endforeach</ul></div>
                                    @endif
                                    @if (! empty($destinationPackageDetails['excludes']))
                                        <div class="series-coverage series-coverage--excluded"><h4><span aria-hidden="true">✕</span> Not included</h4><ul>@foreach ($destinationPackageDetails['excludes'] as $item)<li>{{ $item }}</li>@endforeach</ul></div>
                                    @endif
                                </div>
                            </section>
                        @endif

                        @if ($hasTravelArrangements)
                            <section class="series-details-slide" aria-label="{{ $destination['name'] }} flights and hotels">
                                <p class="series-details-kicker">FLIGHTS &amp; STAYS</p>
                                <h3>Your travel arrangements.</h3>
                                @include('Smart-Muslim.Tour-Package(series).partials.travel-arrangements', ['details' => $destinationPackageDetails, 'hotels' => $destinationPackageDetails['hotels'] ?? [], 'hotelsHeading' => 'Hotels'])
                            </section>
                        @endif

                        @if ($hasPreTravelDetails)
                            <section class="series-details-slide" aria-label="{{ $destination['name'] }} travel essentials and seasonal weather">
                                <p class="series-details-kicker">BEFORE YOU GO</p>
                                <h3>Travel essentials &amp; weather.</h3>
                                @include('Smart-Muslim.Tour-Package(series).partials.essentials-weather', ['essentials' => $destinationPackageDetails['essentials'] ?? [], 'weather' => $destinationPackageDetails['weather'] ?? []])
                            </section>
                        @endif

                        @if (! empty($destination['gallery']))
                            <section class="series-details-slide" aria-label="{{ $destination['name'] }} photo gallery">
                                <p class="series-details-kicker">DESTINATION GALLERY</p>
                                <h3>Discover {{ $destination['name'] }}.</h3>
                                <div class="series-details-generic-gallery">
                                    @foreach ($destination['gallery'] as $photo)
                                        <figure>
                                            <img src="{{ $page->imageUrl($photo['image']) }}" alt="{{ $photo['alt'] }}" loading="lazy">
                                            <figcaption>{{ $photo['caption'] }}</figcaption>
                                        </figure>
                                    @endforeach
                                </div>
                            </section>
                        @endif

                        <section class="series-details-slide" aria-label="{{ $destination['name'] }} tour enquiry">
                            <p class="series-details-kicker">PLAN YOUR TRIP</p>
                            <h3>Ready to explore {{ $destination['name'] }}?</h3>
                            <div class="series-details-cta">
                                <span class="series-details-cta__icon" aria-hidden="true">💬</span>
                                <p>Contact our team for availability and booking assistance.</p>
                                <a class="series-details-open" href="{{ $page->contact_href }}" target="_blank" rel="noreferrer">Ask about this tour <span aria-hidden="true">↗</span></a>
                            </div>
                        </section>
                    </div>
                </div>

                <footer class="series-details-footer">
                    <p class="series-details-count" aria-live="polite">Step 1 of {{ $destinationDetailsSlideCount }}</p>
                    <div class="series-control-buttons" role="group" aria-label="{{ $destination['name'] }} package detail slide controls">
                        <button class="series-details-prev" type="button" aria-label="Previous package details" disabled>←</button>
                        <button class="series-details-next" type="button" aria-label="Next package details">→</button>
                    </div>
                </footer>
            </div>
        </dialog>
    @endforeach
</section>
