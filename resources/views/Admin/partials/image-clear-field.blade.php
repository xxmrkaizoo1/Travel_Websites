{{--
    Image URL + upload with a live preview and a Clear button that falls back to the default look.
    Expects $value, $previewSrc, $urlAttributes, $fileAttributes, $defaultTitle and $defaultHint.
--}}
<div class="homepage-form-field-wide image-clear-field" data-image-field data-saved-image="{{ $value }}" data-saved-image-src="{{ $previewSrc }}">
    <div class="image-clear-field__preview">
        <img src="{{ $previewSrc }}" alt="" data-image-preview @if (blank($value)) hidden @endif>
        <div class="image-clear-field__default" data-image-default @if (filled($value)) hidden @endif>
            <span class="image-clear-field__swatch" aria-hidden="true"></span>
            <strong>{{ $defaultTitle }}</strong>
            <small>{{ $defaultHint }}</small>
        </div>
    </div>
    <div class="image-clear-field__inputs">
        <label class="homepage-form-field"><span>Image URL <small class="homepage-field-hint">Optional · or upload a photo</small></span>
            <span class="homepage-input-with-action">
                <input {!! $urlAttributes !!} value="{{ $value }}" maxlength="2048" data-image-url>
                <button type="button" class="homepage-input-clear" data-image-clear @if (blank($value)) hidden @endif>Clear <span aria-hidden="true">×</span></button>
            </span>
        </label>
        <label class="homepage-form-field"><span>Upload photo <small class="homepage-field-hint">Replaces the URL</small></span><input type="file" {!! $fileAttributes !!} accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" data-image-file></label>
    </div>
</div>
