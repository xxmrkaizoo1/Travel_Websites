<fieldset class="smart-menu-icon-picker">
    <legend>Choose an icon</legend>
    <div class="smart-menu-icon-options">
        @foreach (\App\Models\SmartMuslimMenu::ICONS as $iconKey => $iconLabel)
            <label class="smart-menu-icon-option" title="{{ $iconLabel }}">
                <input type="radio" name="{{ $fieldName }}" value="{{ $iconKey }}" @checked(old($fieldName, $selectedIcon) === $iconKey)>
                <span class="smart-menu-icon-option-face" aria-hidden="true">
                    @include('partials.smart-muslim-menu-icon', ['iconName' => $iconKey])
                </span>
                <span class="smart-menu-icon-option-name">{{ $iconLabel }}</span>
            </label>
        @endforeach
    </div>
</fieldset>
