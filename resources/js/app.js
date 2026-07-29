const landingPage = document.querySelector('[data-landing-page]');

if (landingPage) {
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const revealElements = [...document.querySelectorAll('[data-reveal]')];
    const authPaths = new Set(['/app/login', '/app/register']);
    const authLinks = [...landingPage.querySelectorAll('a[href]')].filter((link) => {
        const destination = new URL(link.href, window.location.href);

        return destination.origin === window.location.origin && authPaths.has(destination.pathname);
    });

    const resetPageTransition = () => {
        document.body.classList.remove('is-leaving');
        landingPage.removeAttribute('aria-busy');
    };

    window.addEventListener('pageshow', resetPageTransition);

    if (!reduceMotion) {
        authLinks.forEach((link) => {
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

                if (document.body.classList.contains('is-leaving')) {
                    return;
                }

                const destination = new URL(link.href, window.location.href);
                const transitionLabel = document.querySelector('[data-transition-label]');

                if (transitionLabel) {
                    transitionLabel.textContent = destination.pathname.endsWith('/register')
                        ? 'Creating your space'
                        : 'Opening your workspace';
                }

                try {
                    sessionStorage.setItem('taskku:auth-transition', 'pending');
                } catch {
                    // The transition still works when browser storage is unavailable.
                }

                landingPage.setAttribute('aria-busy', 'true');
                document.body.classList.add('is-leaving');

                window.setTimeout(() => {
                    window.location.assign(destination.href);
                }, 560);
            });
        });
    }

    if (!reduceMotion) {
        document.documentElement.classList.add('motion-enabled');

        const revealObserver = new IntersectionObserver(
            (entries, observer) => {
                entries.forEach((entry) => {
                    if (!entry.isIntersecting) {
                        return;
                    }

                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                });
            },
            {
                rootMargin: '0px 0px -8% 0px',
                threshold: 0.12,
            },
        );

        requestAnimationFrame(() => {
            revealElements.forEach((element) => revealObserver.observe(element));
        });

        const preview = document.querySelector('[data-tilt]');
        const precisePointer = window.matchMedia('(hover: hover) and (pointer: fine)').matches;

        if (preview && precisePointer) {
            let animationFrame;

            const updateTilt = (event) => {
                const bounds = preview.getBoundingClientRect();
                const pointerX = (event.clientX - bounds.left) / bounds.width - 0.5;
                const pointerY = (event.clientY - bounds.top) / bounds.height - 0.5;

                cancelAnimationFrame(animationFrame);
                animationFrame = requestAnimationFrame(() => {
                    preview.style.setProperty('--tilt-x', `${pointerY * -1.4}deg`);
                    preview.style.setProperty('--tilt-y', `${pointerX * 1.8}deg`);
                });
            };

            const resetTilt = () => {
                cancelAnimationFrame(animationFrame);
                preview.style.setProperty('--tilt-x', '0deg');
                preview.style.setProperty('--tilt-y', '0deg');
            };

            preview.addEventListener('pointermove', updateTilt, { passive: true });
            preview.addEventListener('pointerleave', resetTilt, { passive: true });
        }
    } else {
        revealElements.forEach((element) => element.classList.add('is-visible'));
    }
}
