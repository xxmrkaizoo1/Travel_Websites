{{-- Popup album slots for one destination. $index is null inside the add-destination template; the gallery editor script names those fields. --}}
@php
    $albumPhotos = array_values(array_filter($photos ?? [], 'is_array'));
    $filledCount = count(array_filter($albumPhotos, fn (array $photo): bool => filled($photo['image'] ?? '')));
@endphp
<div class="gallery-album-editor-toolbar">
    <p>Click <strong>Preview popup</strong> to see exactly what visitors get when they click this postcard, including photos you have not saved yet.</p>
    <button class="gallery-album-preview-button" type="button" data-gallery-album-preview>Preview popup <span aria-hidden="true">↗</span></button>
</div>
<details class="gallery-album-editor" @if ($filledCount === 0 && $index !== null) open @endif>
    <summary>
        <span>Popup gallery photos</span>
        <small>{{ $filledCount }} of {{ \App\Models\GalleryPage::MAX_ALBUM_PHOTOS }} · shown when visitors click this postcard. Empty slots are ignored.</small>
    </summary>
    <div class="gallery-album-slots">
        @for ($slot = 0; $slot < \App\Models\GalleryPage::MAX_ALBUM_PHOTOS; $slot++)
            @php($albumPhoto = $albumPhotos[$slot] ?? [])
            <div class="gallery-album-slot">
                <span class="gallery-album-slot-number" aria-hidden="true">{{ $slot + 1 }}</span>
                @if (! empty($albumPhoto['image']) && $page !== null)
                    <img src="{{ $page->imageUrl($albumPhoto['image']) }}" alt="" loading="lazy">
                @endif
                <label class="homepage-form-field"><span>Photo {{ $slot + 1 }} URL</span><input data-gallery-field="photos][{{ $slot }}][image" data-gallery-optional @if ($index !== null) name="destinations[{{ $index }}][photos][{{ $slot }}][image]" @endif value="{{ $albumPhoto['image'] ?? '' }}" maxlength="2048"></label>
                <label class="homepage-form-field"><span>Or upload</span><input type="file" data-gallery-field="photos][{{ $slot }}][image_file" data-gallery-optional @if ($index !== null) name="destinations[{{ $index }}][photos][{{ $slot }}][image_file]" @endif accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"></label>
                <label class="homepage-form-field"><span>Caption</span><input data-gallery-field="photos][{{ $slot }}][caption" data-gallery-optional @if ($index !== null) name="destinations[{{ $index }}][photos][{{ $slot }}][caption]" @endif value="{{ $albumPhoto['caption'] ?? '' }}" maxlength="80" placeholder="e.g. Gyeongbokgung Palace"></label>
            </div>
        @endfor
    </div>
</details>
