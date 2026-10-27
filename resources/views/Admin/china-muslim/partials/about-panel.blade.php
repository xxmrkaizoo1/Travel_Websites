<section class="homepage-form-panel china-about-editor">
    <div class="homepage-form-panel-heading">
        <span class="homepage-form-icon" aria-hidden="true">05</span>
        <div>
            <h2>About this place popup</h2>
            <p>Text and photos shared by every card’s “About this place” popup (the video in section 04 belongs to this card only). Saved with its own button below.</p>
        </div>
    </div>

    <div class="homepage-form-grid">
        <label class="homepage-form-field"><span>Badge</span><input form="china-about-form" name="badge" value="{{ old('badge', $about->badge) }}" required maxlength="60"></label>
        <label class="homepage-form-field"><span>Title</span><input form="china-about-form" name="title" value="{{ old('title', $about->title) }}" required maxlength="120"></label>
        <label class="homepage-form-field homepage-form-field-wide"><span>Introduction</span><textarea form="china-about-form" name="intro" rows="3" required maxlength="1500">{{ old('intro', $about->intro) }}</textarea></label>
    </div>

    <h3 class="china-about-editor-title">Quick facts</h3>
    <div class="china-about-editor-facts">
        @foreach ($about->facts as $index => $fact)
            <div class="china-about-editor-fact">
                <label class="homepage-form-field"><span>Label</span><input form="china-about-form" name="facts[{{ $index }}][label]" value="{{ old("facts.$index.label", $fact['label']) }}" required maxlength="40"></label>
                <label class="homepage-form-field"><span>Value</span><input form="china-about-form" name="facts[{{ $index }}][value]" value="{{ old("facts.$index.value", $fact['value']) }}" required maxlength="120"></label>
            </div>
        @endforeach
    </div>

    <div class="homepage-form-grid">
        <label class="homepage-form-field homepage-form-field-wide"><span>Muslim-friendly info</span><textarea form="china-about-form" name="body" rows="3" required maxlength="1500">{{ old('body', $about->body) }}</textarea></label>
        <label class="homepage-form-field homepage-form-field-wide"><span>Travel note</span><input form="china-about-form" name="note" value="{{ old('note', $about->note) }}" required maxlength="300"></label>
    </div>

    <h3 class="china-about-editor-title">Gallery photos</h3>
    @if (count($about->gallery) > 0)
        <p class="homepage-field-hint">Tick a photo to remove it when you save.</p>
        <div class="china-about-thumbs">
            @foreach ($about->gallery as $photo)
                <label class="china-about-admin-photo">
                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($photo) }}" alt="" loading="lazy">
                    <span><input form="china-about-form" type="checkbox" name="remove_photos[]" value="{{ $photo }}"> Remove</span>
                </label>
            @endforeach
        </div>
    @else
        @php($cardPhotos = \App\Models\ChinaMuslimPackage::query()->orderBy('position')->orderBy('id')->get()->map->imageUrl()->unique()->take(8))
        @if ($cardPhotos->isNotEmpty())
            <p class="homepage-field-hint">Currently shown in the popup (from your card photos). Upload photos below to replace them.</p>
            <div class="china-about-thumbs">
                @foreach ($cardPhotos as $cardPhoto)
                    <span class="china-about-admin-photo"><img src="{{ $cardPhoto }}" alt="" loading="lazy"></span>
                @endforeach
            </div>
        @endif
    @endif
    <input form="china-about-form" type="file" name="new_photos[]" multiple accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">

    <div class="china-about-editor-actions">
        <button class="admin-secondary-button china-about-clear" type="button" data-about-clear>Clear all</button>
        <button class="admin-primary-button" type="submit" form="china-about-form">Save popup <span aria-hidden="true">→</span></button>
    </div>
</section>
