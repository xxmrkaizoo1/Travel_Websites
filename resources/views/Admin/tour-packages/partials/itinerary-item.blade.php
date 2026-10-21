@php
    $base = "package[itineraries][$i]";
    $days = array_values(array_filter($row['days'] ?? [], 'is_array'));
    $days = $days ?: [[]];
@endphp
<fieldset class="island-entry-panel tour-package-itinerary-panel" data-repeater-item>
    <legend>Itinerary <span data-repeater-number></span></legend>
    <label class="homepage-form-field"><span>Duration</span><input name="{{ $base }}[duration]" value="{{ $row['duration'] ?? '' }}" maxlength="60" placeholder="4D3N"></label>
    <label class="homepage-form-field"><span>Title</span><input name="{{ $base }}[title]" value="{{ $row['title'] ?? '' }}" maxlength="120"></label>
    <label class="homepage-form-field homepage-form-field-wide"><span>Short summary</span><input name="{{ $base }}[summary]" value="{{ $row['summary'] ?? '' }}" maxlength="255"></label>
    <label class="homepage-form-field"><span>Button label</span><input name="{{ $base }}[cta_label]" value="{{ $row['cta_label'] ?? '' }}" maxlength="120"></label>
    <label class="homepage-form-field"><span>Button URL <small class="homepage-field-hint">Full http(s) URL, e.g. WhatsApp link</small></span><input type="url" name="{{ $base }}[cta_url]" value="{{ $row['cta_url'] ?? '' }}" maxlength="2048"></label>
    <label class="tour-package-checkbox">
        <input type="hidden" name="{{ $base }}[featured]" value="0">
        <input type="checkbox" name="{{ $base }}[featured]" value="1" @checked(! empty($row['featured']))>
        <span>Highlight this itinerary as featured</span>
    </label>

    <div class="tour-package-repeater" data-repeater data-token="__J__" data-max="15">
        <h3>Day by day</h3>
        <div class="island-destination-fields-grid" data-repeater-list>
            @foreach ($days as $dayIndex => $day)
                @include('Admin.tour-packages.partials.day-item', ['i' => $i, 'j' => $dayIndex, 'row' => $day])
            @endforeach
        </div>
        <template data-repeater-template>
            @include('Admin.tour-packages.partials.day-item', ['i' => $i, 'j' => '__J__', 'row' => []])
        </template>
        <button class="island-add-slide" type="button" data-repeater-add>Add day <span aria-hidden="true">＋</span></button>
    </div>

    <button class="island-remove-slide" type="button" data-repeater-remove>Remove itinerary</button>
</fieldset>
