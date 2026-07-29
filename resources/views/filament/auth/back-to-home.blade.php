<nav class="taskku-auth-home-link" aria-label="Landing page navigation">
    <a href="{{ route('home') }}" data-home-transition>
        <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd" d="M17 10a.75.75 0 0 1-.75.75H5.56l3.22 3.22a.75.75 0 1 1-1.06 1.06l-4.5-4.5a.75.75 0 0 1 0-1.06l4.5-4.5a.75.75 0 0 1 1.06 1.06L5.56 9.25h10.69A.75.75 0 0 1 17 10Z" clip-rule="evenodd" />
        </svg>

        <span>Back to home</span>
    </a>
</nav>

<div class="taskku-auth-page-transition" aria-hidden="true">
    <div class="taskku-auth-transition-content">
        <span class="taskku-auth-transition-mark">✓</span>
        <span>Returning home</span>
    </div>
</div>

<script data-navigate-once>
    (() => {
        const link = document.querySelector('[data-home-transition]');
        const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        if (! link || reduceMotion || link.dataset.transitionReady === 'true') {
            return;
        }

        link.dataset.transitionReady = 'true';

        const resetTransition = () => {
            document.documentElement.classList.remove('taskku-auth-is-leaving');
            document.querySelector('.fi-simple-layout')?.removeAttribute('aria-busy');
        };

        window.addEventListener('pageshow', resetTransition);

        link.addEventListener('click', (event) => {
            const isModifiedClick = event.button !== 0
                || event.metaKey
                || event.ctrlKey
                || event.shiftKey
                || event.altKey;

            if (event.defaultPrevented || isModifiedClick || link.target === '_blank') {
                return;
            }

            event.preventDefault();

            if (document.documentElement.classList.contains('taskku-auth-is-leaving')) {
                return;
            }

            document.querySelector('.fi-simple-layout')?.setAttribute('aria-busy', 'true');
            document.documentElement.classList.add('taskku-auth-is-leaving');

            window.setTimeout(() => {
                window.location.assign(link.href);
            }, 560);
        });
    })();
</script>
