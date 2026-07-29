@php
    use App\Enums\DisplayDensity;
    use App\Models\User;
    use Illuminate\Support\Facades\Auth;

    $user = Auth::user();
    $theme = $user instanceof User
        ? ($user->settings?->theme ?? 'system')
        : 'system';
    $density = $user instanceof User
        ? ($user->settings?->density?->value ?? DisplayDensity::Comfortable->value)
        : DisplayDensity::Comfortable->value;

    if (! in_array($theme, ['light', 'dark', 'system'], true)) {
        $theme = 'system';
    }
@endphp

<script data-navigate-once>
    (() => {
        const allowedThemes = ['light', 'dark', 'system'];
        const savedTheme = @js($theme);
        const allowedDensities = ['comfortable', 'compact'];
        const savedDensity = @js($density);

        if (allowedThemes.includes(savedTheme)) {
            localStorage.setItem('theme', savedTheme);
        }

        if (allowedDensities.includes(savedDensity)) {
            document.documentElement.dataset.density = savedDensity;
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

        window.addEventListener('taskku-density-updated', (event) => {
            const density = event.detail.density;

            if (allowedDensities.includes(density)) {
                document.documentElement.dataset.density = density;
            }
        });
    })();
</script>
