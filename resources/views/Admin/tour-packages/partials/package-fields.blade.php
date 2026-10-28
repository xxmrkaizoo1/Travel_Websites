@php
    $values = old('package', $package);
    $text = fn (string $key): string => (string) ($values[$key] ?? '');
    $rows = fn (string $key): array => array_values(array_filter($values[$key] ?? [], 'is_array'));
    $lines = fn (string $key): array => array_values(array_filter($values[$key] ?? [], 'is_string'));

    $itineraries = $rows('itineraries') ?: [[]];
    $gallery = $rows('gallery');
    $hotels = $rows('hotels');
    $inclusions = $lines('inclusions') ?: [''];
    $exclusions = $lines('exclusions') ?: [''];
@endphp

<section class="homepage-form-panel">
    <div class="homepage-form-panel-heading">
        <span class="homepage-form-icon" aria-hidden="true">04</span>
        <div><h2>Itineraries</h2><p>Each itinerary is a card with a day-by-day plan and a booking button.</p></div>
    </div>
    <div class="homepage-form-grid">
        <label class="homepage-form-field"><span>Eyebrow</span><input name="package[itineraries_eyebrow]" value="{{ $text('itineraries_eyebrow') }}" required maxlength="120"></label>
        <label class="homepage-form-field"><span>Section headline</span><input name="package[itineraries_headline]" value="{{ $text('itineraries_headline') }}" required maxlength="160"></label>
        <label class="homepage-form-field homepage-form-field-wide"><span>Section text</span><input name="package[itineraries_body]" value="{{ $text('itineraries_body') }}" required maxlength="255"></label>
    </div>
    <div class="tour-package-repeater" data-repeater data-token="__I__" data-max="8">
        <div class="island-destination-fields-grid tour-package-itinerary-list" data-repeater-list>
            @foreach ($itineraries as $index => $itinerary)
                @include('Admin.tour-packages.partials.itinerary-item', ['i' => $index, 'row' => $itinerary])
            @endforeach
        </div>
        <template data-repeater-template>
            @include('Admin.tour-packages.partials.itinerary-item', ['i' => '__I__', 'row' => []])
        </template>
        <button class="island-add-slide" type="button" data-repeater-add>Add itinerary <span aria-hidden="true">＋</span></button>
    </div>
</section>

<section class="homepage-form-panel">
    <div class="homepage-form-panel-heading">
        <span class="homepage-form-icon" aria-hidden="true">05</span>
        <div><h2>Photo slideshow</h2><p>The full-screen showcase on the package page. Add at least 2 photos for arrows, thumbnails and autoplay; 4–6 landscape photos work best. Left empty, the hero and intro photos are shown instead.</p></div>
    </div>
    <div class="homepage-form-grid">
        <label class="homepage-form-field"><span>Eyebrow</span><input name="package[gallery_eyebrow]" value="{{ $text('gallery_eyebrow') }}" required maxlength="120"></label>
        <label class="homepage-form-field"><span>Section headline</span><input name="package[gallery_headline]" value="{{ $text('gallery_headline') }}" required maxlength="160"></label>
        <label class="homepage-form-field homepage-form-field-wide"><span>Section text</span><input name="package[gallery_body]" value="{{ $text('gallery_body') }}" required maxlength="255"></label>
    </div>
    <div class="tour-package-repeater" data-repeater data-token="__I__" data-max="{{ $maxSlides }}">
        <div class="island-destination-fields-grid" data-repeater-list>
            @foreach ($gallery as $index => $photo)
                @include('Admin.tour-packages.partials.gallery-item', ['i' => $index, 'row' => $photo])
            @endforeach
        </div>
        <template data-repeater-template>
            @include('Admin.tour-packages.partials.gallery-item', ['i' => '__I__', 'row' => []])
        </template>
        <button class="island-add-slide" type="button" data-repeater-add>Add photo <span aria-hidden="true">＋</span></button>
    </div>
</section>

<section class="homepage-form-panel">
    <div class="homepage-form-panel-heading">
        <span class="homepage-form-icon" aria-hidden="true">06</span>
        <div><h2>Price includes / excludes</h2><p>One line per item.</p></div>
    </div>
    <div class="homepage-form-grid">
        <label class="homepage-form-field"><span>Eyebrow</span><input name="package[details_eyebrow]" value="{{ $text('details_eyebrow') }}" required maxlength="120"></label>
        <label class="homepage-form-field"><span>Section headline</span><input name="package[details_headline]" value="{{ $text('details_headline') }}" required maxlength="160"></label>
        <label class="homepage-form-field homepage-form-field-wide"><span>Section text</span><input name="package[details_body]" value="{{ $text('details_body') }}" required maxlength="255"></label>
    </div>
    <div class="tour-package-two-column">
        @foreach (['inclusions' => ['Price includes', $inclusions], 'exclusions' => ['Price does not include', $exclusions]] as $list => [$title, $items])
            <div class="tour-package-repeater" data-repeater data-token="__I__" data-max="20">
                <h3>{{ $title }}</h3>
                <div data-repeater-list>
                    @foreach ($items as $index => $item)
                        @include('Admin.tour-packages.partials.line-item', ['list' => $list, 'label' => $title, 'i' => $index, 'row' => $item])
                    @endforeach
                </div>
                <template data-repeater-template>
                    @include('Admin.tour-packages.partials.line-item', ['list' => $list, 'label' => $title, 'i' => '__I__', 'row' => ''])
                </template>
                <button class="island-add-slide" type="button" data-repeater-add>Add line <span aria-hidden="true">＋</span></button>
            </div>
        @endforeach
    </div>
</section>

<section class="homepage-form-panel">
    <div class="homepage-form-panel-heading">
        <span class="homepage-form-icon" aria-hidden="true">07</span>
        <div><h2>Accommodation</h2><p>Hotels used on this package (up to 5).</p></div>
    </div>
    <div class="homepage-form-grid">
        <label class="homepage-form-field"><span>Eyebrow</span><input name="package[accommodation_eyebrow]" value="{{ $text('accommodation_eyebrow') }}" required maxlength="120"></label>
        <label class="homepage-form-field"><span>Section headline</span><input name="package[accommodation_headline]" value="{{ $text('accommodation_headline') }}" required maxlength="160"></label>
        <label class="homepage-form-field homepage-form-field-wide"><span>Section text</span><input name="package[accommodation_body]" value="{{ $text('accommodation_body') }}" required maxlength="255"></label>
    </div>
    <div class="tour-package-repeater" data-repeater data-token="__I__" data-max="5">
        <div class="island-destination-fields-grid" data-repeater-list>
            @foreach ($hotels as $index => $hotel)
                @include('Admin.tour-packages.partials.hotel-item', ['i' => $index, 'row' => $hotel])
            @endforeach
        </div>
        <template data-repeater-template>
            @include('Admin.tour-packages.partials.hotel-item', ['i' => '__I__', 'row' => []])
        </template>
        <button class="island-add-slide" type="button" data-repeater-add>Add hotel <span aria-hidden="true">＋</span></button>
    </div>
</section>
