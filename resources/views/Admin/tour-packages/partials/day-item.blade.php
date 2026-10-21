@php($base = "package[itineraries][$i][days][$j]")
<fieldset class="island-entry-panel" data-repeater-item>
    <legend>Day <span data-repeater-number></span></legend>
    <label class="homepage-form-field"><span>Day title</span><input name="{{ $base }}[title]" value="{{ $row['title'] ?? '' }}" maxlength="120"></label>
    <label class="homepage-form-field"><span>Meals <small class="homepage-field-hint">Optional, e.g. Breakfast, Lunch</small></span><input name="{{ $base }}[meals]" value="{{ $row['meals'] ?? '' }}" maxlength="120"></label>
    <label class="homepage-form-field homepage-form-field-wide"><span>Description</span><textarea name="{{ $base }}[body]" maxlength="1000">{{ $row['body'] ?? '' }}</textarea></label>
    <button class="island-remove-slide" type="button" data-repeater-remove>Remove day</button>
</fieldset>
