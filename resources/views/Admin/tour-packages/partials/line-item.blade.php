<div class="tour-package-line-item" data-repeater-item>
    <input name="package[{{ $list }}][{{ $i }}]" value="{{ $row ?? '' }}" maxlength="255" aria-label="{{ $label }}">
    <button class="island-remove-slide" type="button" data-repeater-remove aria-label="Remove line">Remove</button>
</div>
