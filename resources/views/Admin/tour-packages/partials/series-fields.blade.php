@php
    $maxDestinations = 6;
    $seriesValues = old('series', $page->package_data['series'] ?? []);
    $blankDestination = [
        'name' => '', 'country' => '', 'tagline' => '', 'intro' => '', 'flag' => '',
        'advantages' => [['icon' => '', 'title' => '', 'body' => '']],
        'gallery' => [['image' => '', 'alt' => '', 'caption' => '']],
        'package_details' => [
            'duration' => '', 'headline' => '', 'travel_dates' => '', 'price_from' => '', 'tour_code' => '', 'note' => '', 'itinerary_note' => '',
            'departures' => [['date' => '', 'price' => '', 'availability' => '']],
            'itinerary' => [['day' => '', 'title' => '', 'places' => '', 'meals' => '', 'transfer' => '']],
            'includes' => [], 'excludes' => [],
            'outbound' => ['flight' => '', 'route' => '', 'time' => '', 'duration' => ''],
            'return' => ['flight' => '', 'route' => '', 'time' => '', 'duration' => ''],
            'hotels' => [['name' => '', 'room' => '', 'location' => '', 'note' => '']],
            'essentials' => ['visa' => '', 'arrival_card' => '', 'currency' => '', 'time' => '', 'language' => ''],
            'weather' => [['season' => '', 'temperature' => '', 'description' => '']],
        ],
    ];
    $filledCount = count($seriesValues);

    for ($spareIndex = $filledCount; $spareIndex < $maxDestinations; $spareIndex++) {
        $seriesValues[$spareIndex] = $blankDestination;
    }
@endphp

<section class="homepage-form-panel">
    <div class="homepage-form-panel-heading">
        <span class="homepage-form-icon" aria-hidden="true">04</span>
        <div><h2>Series destinations</h2><p>Section text above the destination tabs. Up to {{ $maxDestinations }} destinations: fill in a "New destination" slide to add a country, or clear a destination's name, country, tagline and introduction to remove it.</p></div>
    </div>
    <div class="homepage-form-grid">
        <label class="homepage-form-field"><span>Eyebrow</span><input name="destinations_eyebrow" value="{{ old('destinations_eyebrow', $page->destinations_eyebrow) }}" required maxlength="120"></label>
        <label class="homepage-form-field"><span>Section headline</span><input name="destinations_headline" value="{{ old('destinations_headline', $page->destinations_headline) }}" required maxlength="160"></label>
        <label class="homepage-form-field homepage-form-field-wide"><span>Section text</span><input name="destinations_body" value="{{ old('destinations_body', $page->destinations_body) }}" required maxlength="255"></label>
    </div>
</section>

@foreach ($seriesValues as $index => $destination)
    @php
        $isSpare = trim(($destination['name'] ?? '').($destination['country'] ?? '')) === '';
        $storedPackageDetails = $destination['package_details'] ?? [];
        $packageDetails = array_replace($blankDestination['package_details'], $storedPackageDetails);
        $packageDetails['hotels'] = $storedPackageDetails['hotels'] ?? [];

        if ($packageDetails['hotels'] === [] && ! empty($storedPackageDetails['hotel']['name'])) {
            $packageDetails['hotels'] = [[
                'name' => $storedPackageDetails['hotel']['name'],
                'room' => $storedPackageDetails['hotel']['room'] ?? '',
                'location' => $storedPackageDetails['hotel']['location'] ?? '',
                'note' => $storedPackageDetails['hotel']['note'] ?? '',
            ]];
        } elseif ($packageDetails['hotels'] === [] && $isSpare) {
            $packageDetails['hotels'] = $blankDestination['package_details']['hotels'];
        }

        $packageDetails['includes_text'] = implode("\n", $packageDetails['includes']);
        $packageDetails['excludes_text'] = implode("\n", $packageDetails['excludes']);
        $hasEditablePackageDetails = in_array($destination['name'] ?? '', ['Seoul', 'Kunming', 'Da Nang'], true);
        $showPackageDetails = $hasEditablePackageDetails || $isSpare;
    @endphp
    <section class="homepage-form-panel">
        <div class="homepage-form-panel-heading">
            <span class="homepage-form-icon" aria-hidden="true">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span>
            <div>
                <h2>{{ $isSpare ? 'New destination' : ($destination['name'] ?? 'Destination') }}</h2>
                <p>{{ $isSpare ? 'Leave empty to skip. Fill in to add another country to the series.' : 'Name, flag, advantages and photos for this destination.' }}</p>
            </div>
            @unless ($isSpare)
                <button type="button" class="series-clear-button" data-clear-destination>Clear this destination</button>
            @endunless
        </div>
        <div class="homepage-form-grid">
            <label class="homepage-form-field"><span>City / destination</span><input name="series[{{ $index }}][name]" value="{{ $destination['name'] ?? '' }}" maxlength="60" data-series-name data-series-spare="{{ $isSpare ? 'true' : 'false' }}"></label>
            <label class="homepage-form-field"><span>Country</span><input name="series[{{ $index }}][country]" value="{{ $destination['country'] ?? '' }}" maxlength="60"></label>
            <label class="homepage-form-field homepage-form-field-wide"><span>Tagline</span><input name="series[{{ $index }}][tagline]" value="{{ $destination['tagline'] ?? '' }}" maxlength="255"></label>
            <label class="homepage-form-field homepage-form-field-wide"><span>Introduction</span><textarea name="series[{{ $index }}][intro]" maxlength="600">{{ $destination['intro'] ?? '' }}</textarea></label>
            <label class="homepage-image-field">
                <span>Flag <small class="homepage-field-hint">Upload to replace</small></span>
                @if (! empty($destination['flag']))
                    <img src="{{ $page->imageUrl($destination['flag']) }}" alt="" loading="lazy">
                @endif
                <input type="hidden" name="series[{{ $index }}][flag]" value="{{ $destination['flag'] ?? '' }}">
                <input type="file" name="series[{{ $index }}][flag_file]" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                @error("series.$index.flag_file") <small>{{ $message }}</small> @enderror
            </label>
        </div>

        @php
            $emojiChoices = ['💰', '🧳', '👥', '🛡️', '✈️', '🏨', '🍜', '🕌', '🚌', '📸', '🌴', '⭐', '🎫', '🗺️', '❤️', '✅', '🌙', '🍽️', '🏖️', '⛰️'];
        @endphp
        <div class="series-repeater" data-series-repeater data-name-prefix="series[{{ $index }}][advantages]" data-row-label="Advantage" data-min-rows="1" data-max-rows="6">
            <div class="series-repeater-viewport">
                <div class="island-destination-fields-grid" data-series-repeater-rows>
                @foreach ($destination['advantages'] ?? [] as $advantage)
                    <fieldset class="island-entry-panel" data-series-repeat-row>
                        <legend data-series-row-label>Advantage {{ $loop->iteration }}</legend>
                        <div class="homepage-form-field emoji-field">
                            <span>Icon <small class="homepage-field-hint">Pick an emoji or type your own</small></span>
                            <input class="emoji-field-input" data-series-field="icon" value="{{ $advantage['icon'] ?? '' }}" maxlength="16" placeholder="✓" aria-label="Advantage icon">
                            <div class="emoji-picker" role="group" aria-label="Choose an emoji">
                                @foreach ($emojiChoices as $emoji)
                                    <button type="button" class="emoji-picker-option" data-emoji="{{ $emoji }}" aria-label="Use {{ $emoji }}">{{ $emoji }}</button>
                                @endforeach
                            </div>
                        </div>
                        <label class="homepage-form-field"><span>Title</span><input data-series-field="title" value="{{ $advantage['title'] ?? '' }}" maxlength="120"></label>
                        <label class="homepage-form-field"><span>Text</span><textarea data-series-field="body" maxlength="500">{{ $advantage['body'] ?? '' }}</textarea></label>
                        <button class="island-remove-slide" type="button" data-series-remove-row>Remove advantage</button>
                    </fieldset>
                @endforeach
                </div>
            </div>
            <div class="island-gallery-editor-actions">
                <div class="series-repeater-navigation" role="group" aria-label="Advantage card navigation">
                    <button type="button" data-series-previous aria-label="Previous advantage" disabled>←</button>
                    <span data-series-row-count aria-live="polite">{{ count($destination['advantages'] ?? []) ? 'Advantage 1 of '.count($destination['advantages']) : '0 of 6 advantages' }}</span>
                    <button type="button" data-series-next aria-label="Next advantage">→</button>
                </div>
                <button class="island-add-slide" type="button" data-series-add-row>Add advantage <span aria-hidden="true">＋</span></button>
            </div>
            <template data-series-row-template>
                <fieldset class="island-entry-panel" data-series-repeat-row>
                    <legend data-series-row-label></legend>
                    <div class="homepage-form-field emoji-field">
                        <span>Icon <small class="homepage-field-hint">Pick an emoji or type your own</small></span>
                        <input class="emoji-field-input" data-series-field="icon" maxlength="16" placeholder="✓" aria-label="Advantage icon">
                        <div class="emoji-picker" role="group" aria-label="Choose an emoji">
                            @foreach ($emojiChoices as $emoji)
                                <button type="button" class="emoji-picker-option" data-emoji="{{ $emoji }}" aria-label="Use {{ $emoji }}">{{ $emoji }}</button>
                            @endforeach
                        </div>
                    </div>
                    <label class="homepage-form-field"><span>Title</span><input data-series-field="title" maxlength="120"></label>
                    <label class="homepage-form-field"><span>Text</span><textarea data-series-field="body" maxlength="500"></textarea></label>
                    <button class="island-remove-slide" type="button" data-series-remove-row>Remove advantage</button>
                </fieldset>
            </template>
        </div>

        <div class="series-repeater" data-series-repeater data-name-prefix="series[{{ $index }}][gallery]" data-row-label="Photo" data-min-rows="0" data-max-rows="8">
            <div class="series-repeater-viewport">
                <div class="island-destination-fields-grid" data-series-repeater-rows>
                @foreach ($destination['gallery'] ?? [] as $photo)
                    <fieldset class="island-entry-panel" data-series-repeat-row>
                        <legend data-series-row-label>Photo {{ $loop->iteration }}</legend>
                        @if (! empty($photo['image']))
                            <img class="island-slide-preview" src="{{ $page->imageUrl($photo['image']) }}" alt="" loading="lazy" style="width:100%;height:120px;object-fit:cover;border-radius:12px">
                        @endif
                        <input type="hidden" data-series-field="image" value="{{ $photo['image'] ?? '' }}">
                        <label class="homepage-form-field"><span>{{ empty($photo['image']) ? 'Upload photo' : 'Replace photo' }}</span><input type="file" data-series-field="image_file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"></label>
                        <label class="homepage-form-field"><span>Alt text</span><input data-series-field="alt" value="{{ $photo['alt'] ?? '' }}" maxlength="255"></label>
                        <label class="homepage-form-field"><span>Caption</span><input data-series-field="caption" value="{{ $photo['caption'] ?? '' }}" maxlength="120"></label>
                        <button class="island-remove-slide" type="button" data-series-remove-row>Remove photo</button>
                    </fieldset>
                @endforeach
                </div>
            </div>
            <div class="island-gallery-editor-actions">
                <div class="series-repeater-navigation" role="group" aria-label="Photo card navigation">
                    <button type="button" data-series-previous aria-label="Previous photo" disabled>←</button>
                    <span data-series-row-count aria-live="polite">{{ count($destination['gallery'] ?? []) ? 'Photo 1 of '.count($destination['gallery']) : '0 of 8 photos' }}</span>
                    <button type="button" data-series-next aria-label="Next photo">→</button>
                </div>
                <button class="island-add-slide" type="button" data-series-add-row>Add photo <span aria-hidden="true">＋</span></button>
            </div>
            <template data-series-row-template>
                <fieldset class="island-entry-panel" data-series-repeat-row>
                    <legend data-series-row-label></legend>
                    <input type="hidden" data-series-field="image" value="">
                    <label class="homepage-form-field"><span>Upload photo</span><input type="file" data-series-field="image_file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"></label>
                    <label class="homepage-form-field"><span>Alt text</span><input data-series-field="alt" maxlength="255"></label>
                    <label class="homepage-form-field"><span>Caption</span><input data-series-field="caption" maxlength="120"></label>
                    <button class="island-remove-slide" type="button" data-series-remove-row>Remove photo</button>
                </fieldset>
            </template>
        </div>

        <details class="series-package-editor" data-series-package-editor @if (! $showPackageDetails) hidden @endif @if ($hasEditablePackageDetails) open @endif>
            <summary>{{ $hasEditablePackageDetails ? $destination['name'].' popup package details' : 'Seoul, Kunming or Da Nang popup package details' }}</summary>
            <p class="homepage-form-hint">These fields power the Seoul, Kunming and Da Nang package popups on the public page. Leave this section empty for other destinations.</p>

            <div class="homepage-form-grid">
                <label class="homepage-form-field"><span>Duration</span><input name="series[{{ $index }}][package_details][duration]" value="{{ $packageDetails['duration'] }}" maxlength="80" placeholder="5 days · 4 nights"></label>
                <label class="homepage-form-field"><span>Package headline</span><input name="series[{{ $index }}][package_details][headline]" value="{{ $packageDetails['headline'] }}" maxlength="160"></label>
                <label class="homepage-form-field"><span>Travel dates</span><input name="series[{{ $index }}][package_details][travel_dates]" value="{{ $packageDetails['travel_dates'] }}" maxlength="120"></label>
                <label class="homepage-form-field"><span>Starting price</span><input name="series[{{ $index }}][package_details][price_from]" value="{{ $packageDetails['price_from'] }}" maxlength="80"></label>
                <label class="homepage-form-field"><span>Tour code</span><input name="series[{{ $index }}][package_details][tour_code]" value="{{ $packageDetails['tour_code'] }}" maxlength="60"></label>
                <label class="homepage-form-field homepage-form-field-wide"><span>Package note</span><textarea name="series[{{ $index }}][package_details][note]" maxlength="500">{{ $packageDetails['note'] }}</textarea></label>
                <label class="homepage-form-field homepage-form-field-wide"><span>Itinerary note</span><textarea name="series[{{ $index }}][package_details][itinerary_note]" maxlength="500">{{ $packageDetails['itinerary_note'] ?? '' }}</textarea></label>
            </div>

            <h3 class="series-package-editor-subheading">Travel dates &amp; availability</h3>
            <div class="series-repeater" data-series-repeater data-name-prefix="series[{{ $index }}][package_details][departures]" data-row-label="Departure" data-min-rows="0" data-max-rows="10">
                <div class="series-repeater-viewport">
                    <div class="island-destination-fields-grid" data-series-repeater-rows>
                    @foreach ($packageDetails['departures'] as $departure)
                        <fieldset class="island-entry-panel" data-series-repeat-row>
                            <legend data-series-row-label>Departure {{ $loop->iteration }}</legend>
                            <label class="homepage-form-field"><span>Date</span><input data-series-field="date" value="{{ $departure['date'] ?? '' }}" maxlength="80"></label>
                            <label class="homepage-form-field"><span>Price</span><input data-series-field="price" value="{{ $departure['price'] ?? '' }}" maxlength="80"></label>
                            <label class="homepage-form-field"><span>Availability</span><input data-series-field="availability" value="{{ $departure['availability'] ?? '' }}" maxlength="80"></label>
                            <button class="island-remove-slide" type="button" data-series-remove-row>Remove departure</button>
                        </fieldset>
                    @endforeach
                    </div>
                </div>
                <div class="island-gallery-editor-actions">
                    <div class="series-repeater-navigation" role="group" aria-label="Departure card navigation">
                        <button type="button" data-series-previous aria-label="Previous departure" disabled>←</button>
                        <span data-series-row-count aria-live="polite">{{ count($packageDetails['departures']) ? 'Departure 1 of '.count($packageDetails['departures']) : '0 of 10 departures' }}</span>
                        <button type="button" data-series-next aria-label="Next departure">→</button>
                    </div>
                    <button class="island-add-slide" type="button" data-series-add-row>Add departure <span aria-hidden="true">＋</span></button>
                </div>
                <template data-series-row-template>
                    <fieldset class="island-entry-panel" data-series-repeat-row>
                        <legend data-series-row-label></legend>
                        <label class="homepage-form-field"><span>Date</span><input data-series-field="date" maxlength="80"></label>
                        <label class="homepage-form-field"><span>Price</span><input data-series-field="price" maxlength="80"></label>
                        <label class="homepage-form-field"><span>Availability</span><input data-series-field="availability" maxlength="80"></label>
                        <button class="island-remove-slide" type="button" data-series-remove-row>Remove departure</button>
                    </fieldset>
                </template>
            </div>

            <h3 class="series-package-editor-subheading">Day-by-day itinerary</h3>
            <div class="series-repeater" data-series-repeater data-name-prefix="series[{{ $index }}][package_details][itinerary]" data-row-label="Day" data-min-rows="0" data-max-rows="12">
                <div class="series-repeater-viewport">
                    <div class="island-destination-fields-grid" data-series-repeater-rows>
                    @foreach ($packageDetails['itinerary'] as $day)
                        <fieldset class="island-entry-panel" data-series-repeat-row>
                            <legend data-series-row-label>{{ $day['day'] ?? 'Day '.$loop->iteration }}</legend>
                            <label class="homepage-form-field"><span>Day label</span><input data-series-field="day" value="{{ $day['day'] ?? '' }}" maxlength="30"></label>
                            <label class="homepage-form-field"><span>Day heading</span><input data-series-field="title" value="{{ $day['title'] ?? '' }}" maxlength="120"></label>
                            <label class="homepage-form-field"><span>Places &amp; activities</span><textarea data-series-field="places" maxlength="700">{{ $day['places'] ?? '' }}</textarea></label>
                            <label class="homepage-form-field"><span>Meals</span><input data-series-field="meals" value="{{ $day['meals'] ?? '' }}" maxlength="160"></label>
                            <label class="homepage-form-field"><span>Transfer</span><input data-series-field="transfer" value="{{ $day['transfer'] ?? '' }}" maxlength="160"></label>
                            <button class="island-remove-slide" type="button" data-series-remove-row>Remove day</button>
                        </fieldset>
                    @endforeach
                    </div>
                </div>
                <div class="island-gallery-editor-actions">
                    <div class="series-repeater-navigation" role="group" aria-label="Itinerary day card navigation">
                        <button type="button" data-series-previous aria-label="Previous day" disabled>←</button>
                        <span data-series-row-count aria-live="polite">{{ count($packageDetails['itinerary']) ? 'Day 1 of '.count($packageDetails['itinerary']) : '0 of 12 days' }}</span>
                        <button type="button" data-series-next aria-label="Next day">→</button>
                    </div>
                    <button class="island-add-slide" type="button" data-series-add-row>Add day <span aria-hidden="true">＋</span></button>
                </div>
                <template data-series-row-template>
                    <fieldset class="island-entry-panel" data-series-repeat-row>
                        <legend data-series-row-label></legend>
                        <label class="homepage-form-field"><span>Day label</span><input data-series-field="day" maxlength="30"></label>
                        <label class="homepage-form-field"><span>Day heading</span><input data-series-field="title" maxlength="120"></label>
                        <label class="homepage-form-field"><span>Places &amp; activities</span><textarea data-series-field="places" maxlength="700"></textarea></label>
                        <label class="homepage-form-field"><span>Meals</span><input data-series-field="meals" maxlength="160"></label>
                        <label class="homepage-form-field"><span>Transfer</span><input data-series-field="transfer" maxlength="160"></label>
                        <button class="island-remove-slide" type="button" data-series-remove-row>Remove day</button>
                    </fieldset>
                </template>
            </div>

            <div class="homepage-form-grid">
                <label class="homepage-form-field"><span>Package includes <small class="homepage-field-hint">One item per line</small></span><textarea name="series[{{ $index }}][package_details][includes_text]" maxlength="1500">{{ $packageDetails['includes_text'] }}</textarea></label>
                <label class="homepage-form-field"><span>Package excludes <small class="homepage-field-hint">One item per line</small></span><textarea name="series[{{ $index }}][package_details][excludes_text]" maxlength="1500">{{ $packageDetails['excludes_text'] }}</textarea></label>
            </div>

            <h3 class="series-package-editor-subheading">Flights &amp; hotel</h3>
            <div class="island-destination-fields-grid">
                @foreach (['outbound' => 'Outbound flight', 'return' => 'Return flight'] as $flightKey => $flightLabel)
                    <fieldset class="island-entry-panel">
                        <legend>{{ $flightLabel }}</legend>
                        <label class="homepage-form-field"><span>Flight number</span><input name="series[{{ $index }}][package_details][{{ $flightKey }}][flight]" value="{{ $packageDetails[$flightKey]['flight'] ?? '' }}" maxlength="40"></label>
                        <label class="homepage-form-field"><span>Route</span><input name="series[{{ $index }}][package_details][{{ $flightKey }}][route]" value="{{ $packageDetails[$flightKey]['route'] ?? '' }}" maxlength="120"></label>
                        <label class="homepage-form-field"><span>Flight time</span><input name="series[{{ $index }}][package_details][{{ $flightKey }}][time]" value="{{ $packageDetails[$flightKey]['time'] ?? '' }}" maxlength="80"></label>
                        <label class="homepage-form-field"><span>Duration</span><input name="series[{{ $index }}][package_details][{{ $flightKey }}][duration]" value="{{ $packageDetails[$flightKey]['duration'] ?? '' }}" maxlength="80"></label>
                    </fieldset>
                @endforeach
            </div>
            <div class="series-repeater" data-series-repeater data-name-prefix="series[{{ $index }}][package_details][hotels]" data-row-label="Hotel" data-min-rows="0" data-max-rows="10">
                <div class="series-repeater-viewport">
                    <div class="island-destination-fields-grid" data-series-repeater-rows>
                    @foreach ($packageDetails['hotels'] as $hotel)
                        <fieldset class="island-entry-panel" data-series-repeat-row>
                            <legend data-series-row-label>Hotel {{ $loop->iteration }}</legend>
                            <label class="homepage-form-field"><span>Hotel name</span><input data-series-field="name" value="{{ $hotel['name'] ?? '' }}" maxlength="120"></label>
                            <label class="homepage-form-field"><span>Room type</span><input data-series-field="room" value="{{ $hotel['room'] ?? '' }}" maxlength="120"></label>
                            <label class="homepage-form-field"><span>Location / stay days</span><input data-series-field="location" value="{{ $hotel['location'] ?? '' }}" maxlength="120"></label>
                            <label class="homepage-form-field homepage-form-field-wide"><span>Hotel note</span><textarea data-series-field="note" maxlength="500">{{ $hotel['note'] ?? '' }}</textarea></label>
                            <button class="island-remove-slide" type="button" data-series-remove-row>Remove hotel</button>
                        </fieldset>
                    @endforeach
                    </div>
                </div>
                <div class="island-gallery-editor-actions">
                    <div class="series-repeater-navigation" role="group" aria-label="Hotel card navigation">
                        <button type="button" data-series-previous aria-label="Previous hotel" disabled>←</button>
                        <span data-series-row-count aria-live="polite">{{ count($packageDetails['hotels']) ? 'Hotel 1 of '.count($packageDetails['hotels']) : '0 of 10 hotels' }}</span>
                        <button type="button" data-series-next aria-label="Next hotel">→</button>
                    </div>
                    <button class="island-add-slide" type="button" data-series-add-row>Add hotel <span aria-hidden="true">＋</span></button>
                </div>
                <template data-series-row-template>
                    <fieldset class="island-entry-panel" data-series-repeat-row>
                        <legend data-series-row-label></legend>
                        <label class="homepage-form-field"><span>Hotel name</span><input data-series-field="name" maxlength="120"></label>
                        <label class="homepage-form-field"><span>Room type</span><input data-series-field="room" maxlength="120"></label>
                        <label class="homepage-form-field"><span>Location / stay days</span><input data-series-field="location" maxlength="120"></label>
                        <label class="homepage-form-field homepage-form-field-wide"><span>Hotel note</span><textarea data-series-field="note" maxlength="500"></textarea></label>
                        <button class="island-remove-slide" type="button" data-series-remove-row>Remove hotel</button>
                    </fieldset>
                </template>
            </div>

            <h3 class="series-package-editor-subheading">Travel essentials &amp; weather</h3>
            <div class="homepage-form-grid">
                @foreach (['visa' => 'Visa', 'arrival_card' => 'Arrival card', 'currency' => 'Currency', 'time' => 'Time zone', 'language' => 'Language'] as $essentialKey => $essentialLabel)
                    <label class="homepage-form-field"><span>{{ $essentialLabel }}</span><input name="series[{{ $index }}][package_details][essentials][{{ $essentialKey }}]" value="{{ $packageDetails['essentials'][$essentialKey] ?? '' }}" maxlength="120"></label>
                @endforeach
            </div>
            <div class="series-repeater" data-series-repeater data-name-prefix="series[{{ $index }}][package_details][weather]" data-row-label="Season" data-min-rows="0" data-max-rows="4">
                    <div class="series-repeater-viewport">
                        <div class="island-destination-fields-grid" data-series-repeater-rows>
                        @foreach ($packageDetails['weather'] as $season)
                            <fieldset class="island-entry-panel" data-series-repeat-row>
                            <legend data-series-row-label>Season {{ $loop->iteration }}</legend>
                            <label class="homepage-form-field"><span>Months</span><input data-series-field="season" value="{{ $season['season'] ?? '' }}" maxlength="80"></label>
                            <label class="homepage-form-field"><span>Temperature</span><input data-series-field="temperature" value="{{ $season['temperature'] ?? '' }}" maxlength="80"></label>
                            <label class="homepage-form-field"><span>Season description</span><input data-series-field="description" value="{{ $season['description'] ?? '' }}" maxlength="100"></label>
                            <button class="island-remove-slide" type="button" data-series-remove-row>Remove season</button>
                        </fieldset>
                    @endforeach
                    </div>
                </div>
                <div class="island-gallery-editor-actions">
                    <div class="series-repeater-navigation" role="group" aria-label="Weather season card navigation">
                        <button type="button" data-series-previous aria-label="Previous season" disabled>←</button>
                        <span data-series-row-count aria-live="polite">{{ count($packageDetails['weather']) ? 'Season 1 of '.count($packageDetails['weather']) : '0 of 4 seasons' }}</span>
                        <button type="button" data-series-next aria-label="Next season">→</button>
                    </div>
                    <button class="island-add-slide" type="button" data-series-add-row>Add season <span aria-hidden="true">＋</span></button>
                </div>
                <template data-series-row-template>
                    <fieldset class="island-entry-panel" data-series-repeat-row>
                        <legend data-series-row-label></legend>
                        <label class="homepage-form-field"><span>Months</span><input data-series-field="season" maxlength="80"></label>
                        <label class="homepage-form-field"><span>Temperature</span><input data-series-field="temperature" maxlength="80"></label>
                        <label class="homepage-form-field"><span>Season description</span><input data-series-field="description" maxlength="100"></label>
                        <button class="island-remove-slide" type="button" data-series-remove-row>Remove season</button>
                    </fieldset>
                </template>
            </div>
        </details>
    </section>
@endforeach
