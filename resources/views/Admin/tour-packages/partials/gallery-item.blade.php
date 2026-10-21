@php($base = "package[gallery][$i]")
<fieldset class="island-entry-panel" data-repeater-item>
    <legend>Photo <span data-repeater-number></span></legend>
    @if (! empty($row['image']))
        <img class="tour-package-slide-preview" src="{{ $page->imageUrl($row['image']) }}" alt="" loading="lazy" width="160">
    @endif
    <label class="homepage-form-field"><span>Image URL <small class="homepage-field-hint">Or upload a file below</small></span><input name="{{ $base }}[image]" value="{{ $row['image'] ?? '' }}" maxlength="2048"></label>
    <label class="homepage-form-field"><span>Upload image <small class="homepage-field-hint">Replaces the URL above</small></span><input type="file" name="{{ $base }}[image_file]" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"></label>
    <label class="homepage-form-field"><span>Image alt text</span><input name="{{ $base }}[alt]" value="{{ $row['alt'] ?? '' }}" maxlength="255"></label>
    <label class="homepage-form-field"><span>Eyebrow</span><input name="{{ $base }}[eyebrow]" value="{{ $row['eyebrow'] ?? '' }}" maxlength="120"></label>
    <label class="homepage-form-field"><span>Caption title</span><input name="{{ $base }}[title]" value="{{ $row['title'] ?? '' }}" maxlength="120"></label>
    <button class="island-remove-slide" type="button" data-repeater-remove>Remove photo</button>
</fieldset>
