@php
    $maxDestinations = 6;
    $seriesValues = old('series', $page->package_data['series'] ?? []);
    $blankDestination = [
        'name' => '', 'country' => '', 'tagline' => '', 'intro' => '', 'flag' => '',
        'advantages' => array_fill(0, 4, ['icon' => '', 'title' => '', 'body' => '']),
        'gallery' => array_fill(0, 5, ['image' => '', 'alt' => '', 'caption' => '']),
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
    @php $isSpare = trim(($destination['name'] ?? '').($destination['country'] ?? '')) === ''; @endphp
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
            <label class="homepage-form-field"><span>City / destination</span><input name="series[{{ $index }}][name]" value="{{ $destination['name'] ?? '' }}" maxlength="60">            </label>
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

        <div class="island-destination-fields-grid">
            @foreach ($destination['advantages'] ?? [] as $advantageIndex => $advantage)
                <fieldset class="island-entry-panel">
                    <legend>Advantage {{ $loop->iteration }}</legend>
                    @php
                        $emojiChoices = ['💰', '🧳', '👥', '🛡️', '✈️', '🏨', '🍜', '🕌', '🚌', '📸', '🌴', '⭐', '🎫', '🗺️', '❤️', '✅', '🌙', '🍽️', '🏖️', '⛰️'];
                    @endphp
                    <div class="homepage-form-field emoji-field">
                        <span>Icon <small class="homepage-field-hint">Pick an emoji or type your own</small></span>
                        <input class="emoji-field-input" name="series[{{ $index }}][advantages][{{ $advantageIndex }}][icon]" value="{{ $advantage['icon'] ?? '' }}" maxlength="16" placeholder="✓" aria-label="Advantage icon">
                        <div class="emoji-picker" role="group" aria-label="Choose an emoji">
                            @foreach ($emojiChoices as $emoji)
                                <button type="button" class="emoji-picker-option" data-emoji="{{ $emoji }}" aria-label="Use {{ $emoji }}">{{ $emoji }}</button>
                            @endforeach
                        </div>
                    </div>
                    <label class="homepage-form-field"><span>Title</span><input name="series[{{ $index }}][advantages][{{ $advantageIndex }}][title]" value="{{ $advantage['title'] ?? '' }}" maxlength="120"></label>
                    <label class="homepage-form-field"><span>Text</span><textarea name="series[{{ $index }}][advantages][{{ $advantageIndex }}][body]" maxlength="500">{{ $advantage['body'] ?? '' }}</textarea></label>
                </fieldset>
            @endforeach
        </div>

        <div class="island-destination-fields-grid">
            @foreach ($destination['gallery'] ?? [] as $photoIndex => $photo)
                <fieldset class="island-entry-panel">
                    <legend>Photo {{ $loop->iteration }}</legend>
                    @if (! empty($photo['image']))
                        <img class="island-slide-preview" src="{{ $page->imageUrl($photo['image']) }}" alt="" loading="lazy" style="width:100%;height:120px;object-fit:cover;border-radius:12px">
                    @endif
                    <input type="hidden" name="series[{{ $index }}][gallery][{{ $photoIndex }}][image]" value="{{ $photo['image'] ?? '' }}">
                    <label class="homepage-form-field"><span>{{ empty($photo['image']) ? 'Upload photo' : 'Replace photo' }}</span><input type="file" name="series[{{ $index }}][gallery][{{ $photoIndex }}][image_file]" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"></label>
                    <label class="homepage-form-field"><span>Alt text</span><input name="series[{{ $index }}][gallery][{{ $photoIndex }}][alt]" value="{{ $photo['alt'] ?? '' }}" maxlength="255"></label>
                    <label class="homepage-form-field"><span>Caption</span><input name="series[{{ $index }}][gallery][{{ $photoIndex }}][caption]" value="{{ $photo['caption'] ?? '' }}" maxlength="120"></label>
                </fieldset>
            @endforeach
        </div>
    </section>
@endforeach
