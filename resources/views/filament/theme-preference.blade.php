@php
    $user = \Illuminate\Support\Facades\Auth::user();
    $theme = $user instanceof \App\Models\User
        ? ($user->settings?->theme ?? 'system')
        : 'system';

    if (! in_array($theme, ['light', 'dark', 'system'], true)) {
        $theme = 'system';
    }
@endphp

<script data-navigate-once>
    (() => {
        const allowedThemes = ['light', 'dark', 'system'];
        const savedTheme = @js($theme);

        if (allowedThemes.includes(savedTheme)) {
            localStorage.setItem('theme', savedTheme);
        }

        window.addEventListener('taskku-theme-updated', (event) => {
            const theme = event.detail.theme;

            if (! allowedThemes.includes(theme)) {
                return;
            }

            localStorage.setItem('theme', theme);
            window.dispatchEvent(new CustomEvent('theme-changed', {
                detail: theme,
            }));
        });
    })();
</script>
