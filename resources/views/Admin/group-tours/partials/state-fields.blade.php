{{-- One state's inputs. With a null $index the inputs have no name, for the add-state template; the editor script names them. --}}
@php($fieldName = fn (string $field): string => $index === null ? '' : 'name="states['.$index.']['.$field.']"')
<label class="homepage-form-field"><span>{{ \Illuminate\Support\Str::ucfirst($destination->placeTerm()) }} name</span><input data-gallery-field="name" {!! $fieldName('name') !!} value="{{ $state['name'] ?? '' }}" maxlength="60"></label>
<label class="homepage-form-field"><span>Region</span>
    <select data-gallery-field="region" data-gallery-optional {!! $fieldName('region') !!}>
        @foreach ($destination->regionOptions() as $regionKey => $regionLabel)
            <option value="{{ $regionKey }}" @selected(($state['region'] ?? '') === $regionKey)>{{ $regionLabel }}</option>
        @endforeach
    </select>
</label>
<label class="homepage-form-field"><span>{{ $destination->place_term === 'state' ? 'Capital' : 'Area or province' }} <small class="homepage-field-hint">Optional · shown as a small badge</small></span><input data-gallery-field="capital" data-gallery-optional {!! $fieldName('capital') !!} value="{{ $state['capital'] ?? '' }}" maxlength="60"></label>
@include('Admin.partials.image-clear-field', [
    'value' => (string) ($state['image'] ?? ''),
    'previewSrc' => filled($state['image'] ?? '') ? $destination->imageUrl($state['image']) : '',
    'urlAttributes' => 'data-gallery-field="image" data-gallery-optional '.$fieldName('image'),
    'fileAttributes' => 'data-gallery-field="image_file" data-gallery-optional '.$fieldName('image_file'),
    'defaultTitle' => 'Coloured tile',
    'defaultHint' => 'No photo · the card shows a coloured tile with the initials',
])
<label class="homepage-form-field"><span>Photo alt text <small class="homepage-field-hint">Optional</small></span><input data-gallery-field="alt" data-gallery-optional {!! $fieldName('alt') !!} value="{{ $state['alt'] ?? '' }}" maxlength="255"></label>
<label class="homepage-form-field homepage-form-field-wide"><span>Short description <small class="homepage-field-hint">Optional · one line</small></span><input data-gallery-field="tagline" data-gallery-optional {!! $fieldName('tagline') !!} value="{{ $state['tagline'] ?? '' }}" maxlength="160"></label>
