{{-- Applies the administrator's light, dark or device theme before the page paints, so it never flashes white. --}}
<script>
    (() => {
        const root = document.documentElement;
        const deviceTheme = window.matchMedia('(prefers-color-scheme: dark)');

        // The settings page calls this again when the administrator picks another theme.
        window.applyAdminTheme = (choice) => {
            root.dataset.adminThemeChoice = choice;
            root.dataset.adminTheme = choice === 'dark' || (choice === 'system' && deviceTheme.matches) ? 'dark' : 'light';
        };

        window.applyAdminTheme(@js(auth()->user()?->admin_theme ?? 'light'));
        deviceTheme.addEventListener('change', () => window.applyAdminTheme(root.dataset.adminThemeChoice));
    })();
</script>
