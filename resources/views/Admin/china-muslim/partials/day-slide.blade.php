<div class="china-edit-slide" data-china-slide>
    <div class="china-edit-slide-head">
        <span class="china-edit-slide-badge">DAY <b data-china-slide-number>{{ is_numeric($index) ? str_pad($index + 1, 2, '0', STR_PAD_LEFT) : '' }}</b></span>
        <button class="china-edit-remove" type="button" data-china-remove-slide aria-label="Remove this slide">Remove</button>
    </div>
    <div class="china-edit-slide-grid">
        <label class="homepage-form-field">
            <span>Slide title</span>
            <input name="slides[{{ $index }}][title]" value="{{ $day['title'] ?? '' }}" maxlength="160" placeholder="SHANGHAI – SUZHOU">
            @if (is_numeric($index))
                @error('slides.'.$index.'.title') <small>{{ $message }}</small> @enderror
            @endif
        </label>
        <label class="homepage-form-field">
            <span>Meals</span>
            <input name="slides[{{ $index }}][meals]" value="{{ $day['meals'] ?? '' }}" maxlength="40" placeholder="B,L,D">
        </label>
        <label class="homepage-form-field china-edit-slide-wide">
            <span>Activities <small>One per line</small></span>
            <textarea name="slides[{{ $index }}][items]" rows="5" maxlength="4000">{{ $day['items'] ?? '' }}</textarea>
        </label>
    </div>
</div>
