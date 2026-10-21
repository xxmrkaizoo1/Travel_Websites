@php($base = "package[hotels][$i]")
<fieldset class="island-entry-panel" data-repeater-item>
    <legend>Hotel <span data-repeater-number></span></legend>
    <label class="homepage-form-field"><span>Label <small class="homepage-field-hint">e.g. Bali stay</small></span><input name="{{ $base }}[label]" value="{{ $row['label'] ?? '' }}" maxlength="120"></label>
    <label class="homepage-form-field"><span>Hotel name</span><input name="{{ $base }}[name]" value="{{ $row['name'] ?? '' }}" maxlength="120"></label>
    <label class="homepage-form-field homepage-form-field-wide"><span>Address</span><input name="{{ $base }}[address]" value="{{ $row['address'] ?? '' }}" maxlength="255"></label>
    <label class="homepage-form-field"><span>Phone <small class="homepage-field-hint">Optional</small></span><input name="{{ $base }}[phone]" value="{{ $row['phone'] ?? '' }}" maxlength="40"></label>
    <button class="island-remove-slide" type="button" data-repeater-remove>Remove hotel</button>
</fieldset>
