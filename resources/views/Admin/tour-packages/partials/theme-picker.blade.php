{{--
    Colour theme radio cards for a custom tour page. Expects $selectedTheme.
--}}
<div class="theme-picker" role="radiogroup" aria-label="Theme colour">
    @foreach (\App\Models\TourPackagePage::THEMES as $themeKey => [$themeName, $themeColour, $themeDark])
        <label class="theme-picker__card" style="--swatch: {{ $themeColour }}; --swatch-dark: {{ $themeDark }}">
            <input type="radio" name="theme" value="{{ $themeKey }}" @checked($selectedTheme === $themeKey)>
            <span class="theme-picker__preview" aria-hidden="true">
                <span class="theme-picker__pill"></span>
                <span class="theme-picker__line"></span>
                <span class="theme-picker__line theme-picker__line--short"></span>
                <span class="theme-picker__button"></span>
            </span>
            <strong>{{ $themeName }}</strong>
            <span class="create-type-check" aria-hidden="true">✓</span>
        </label>
    @endforeach
</div>
