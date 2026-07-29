<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta
            name="description"
            content="Taskku brings projects, daily tasks, deadlines, and team workspaces into one calm, focused place."
        >

        <title>Taskku — Make progress feel effortless</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>

    <body class="min-h-screen bg-stone-50 font-sans text-stone-950 antialiased selection:bg-amber-200 selection:text-amber-950" data-landing-page>
        <div class="landing-shell relative isolate overflow-hidden">
            <div
                class="landing-aurora pointer-events-none absolute inset-x-0 top-0 -z-10 h-[48rem] opacity-80"
                style="background:
                    radial-gradient(circle at 15% 10%, rgba(251, 191, 36, 0.2), transparent 32rem),
                    radial-gradient(circle at 90% 5%, rgba(253, 230, 138, 0.45), transparent 26rem);"
            ></div>

            <nav
                class="mx-auto flex max-w-7xl items-center justify-between px-5 py-5 sm:px-8 lg:px-10"
                aria-label="Main navigation"
                data-reveal
                data-reveal-direction="down"
            >
                <a href="{{ route('home') }}" class="group inline-flex items-center gap-2.5" aria-label="Taskku home">
                    <span class="landing-logo-mark flex h-9 w-9 items-center justify-center rounded-xl bg-stone-950 text-lg font-bold text-amber-300 shadow-sm transition group-hover:-rotate-3">
                        ✓
                    </span>
                    <span class="text-lg font-bold tracking-tight">Taskku</span>
                </a>

                <div class="hidden items-center gap-8 text-sm font-medium text-stone-600 md:flex">
                    <a href="#features" class="landing-nav-link transition hover:text-stone-950">Features</a>
                    <a href="#preview" class="landing-nav-link transition hover:text-stone-950">How it works</a>
                </div>

                <div class="flex items-center gap-2 sm:gap-3">
                    <a
                        href="{{ url('/app/login') }}"
                        class="rounded-lg px-3 py-2 text-sm font-semibold text-stone-700 transition hover:bg-white hover:text-stone-950 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-amber-500 sm:px-4"
                    >
                        Sign In
                    </a>
                    <a
                        href="{{ url('/app/register') }}"
                        class="rounded-lg bg-stone-950 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-stone-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-stone-950 sm:px-5"
                    >
                        Get Started
                    </a>
                </div>
            </nav>

            <main>
                <section class="mx-auto max-w-7xl px-5 pb-20 pt-14 sm:px-8 sm:pb-28 sm:pt-20 lg:px-10 lg:pt-24">
                    <div class="mx-auto max-w-4xl text-center">

                        <h1
                            class="text-balance text-5xl font-bold tracking-[-0.045em] text-stone-950 sm:text-6xl lg:text-7xl"
                            data-reveal
                            style="--reveal-delay: 80ms"
                        >
                            Make room for work
                            <span class="relative whitespace-nowrap">
                                <span class="relative z-10">that matters.</span>
                                <span class="landing-highlight absolute inset-x-0 bottom-1 -z-0 h-3 origin-left rounded-full bg-amber-300/70 sm:h-4"></span>
                            </span>
                        </h1>

                        <p
                            class="mx-auto mt-7 max-w-2xl text-pretty text-lg leading-8 text-stone-600 sm:text-xl"
                            data-reveal
                            style="--reveal-delay: 170ms"
                        >
                            Plan projects, focus on today, and keep every workspace moving—without turning productivity into another full-time job.
                        </p>

                        <div
                            class="mt-9 flex flex-col items-center justify-center gap-3 sm:flex-row"
                            data-reveal
                            style="--reveal-delay: 260ms"
                        >
                            <a
                                href="{{ url('/app/register') }}"
                                class="landing-primary-cta inline-flex w-full items-center justify-center gap-2 rounded-xl bg-amber-400 px-6 py-3.5 text-base font-bold text-amber-950 shadow-[0_8px_30px_rgba(245,158,11,0.25)] transition hover:-translate-y-0.5 hover:bg-amber-300 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-amber-500 sm:w-auto"
                            >
                                Get Started right now
                                <span class="landing-arrow" aria-hidden="true">→</span>
                            </a>
                            <a
                                href="{{ url('/app/login') }}"
                                class="inline-flex w-full items-center justify-center rounded-xl border border-stone-300 bg-white/80 px-6 py-3.5 text-base font-semibold text-stone-800 shadow-sm backdrop-blur transition hover:-translate-y-0.5 hover:border-stone-400 hover:bg-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-amber-500 sm:w-auto"
                            >
                                Sign In
                            </a>
                        </div>

                    </div>

                    <div
                        id="preview"
                        class="landing-preview relative mx-auto mt-16 max-w-6xl scroll-mt-8 sm:mt-20"
                        data-reveal
                        data-reveal-distance="large"
                        data-tilt
                    >
                        <div class="landing-preview-glow absolute -inset-5 -z-10 rounded-[2.5rem] bg-gradient-to-r from-amber-200/60 via-orange-100/40 to-yellow-200/60 blur-2xl"></div>

                        <div class="landing-preview-frame overflow-hidden rounded-2xl border border-stone-900/10 bg-stone-950 p-2 shadow-[0_35px_90px_-25px_rgba(28,25,23,0.45)] sm:rounded-3xl sm:p-3">
                            <div class="flex items-center gap-1.5 px-3 py-2.5">
                                <span class="h-2.5 w-2.5 rounded-full bg-red-400"></span>
                                <span class="h-2.5 w-2.5 rounded-full bg-amber-400"></span>
                                <span class="h-2.5 w-2.5 rounded-full bg-emerald-400"></span>
                                <span class="ml-3 text-[11px] font-medium tracking-wide text-stone-500">TASKKU / PRODUCT TEAM</span>
                            </div>

                            <div class="grid min-h-[31rem] overflow-hidden rounded-xl bg-stone-100 sm:grid-cols-[13rem_1fr] lg:grid-cols-[14rem_1fr]">
                                <aside class="hidden border-r border-stone-200 bg-white p-5 sm:block">
                                    <div class="flex items-center gap-2 text-sm font-bold">
                                        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-stone-950 text-xs text-amber-300">✓</span>
                                        Taskku
                                    </div>

                                    <div class="mt-8 space-y-1 text-sm">
                                        <div class="flex items-center gap-2 rounded-lg bg-amber-100 px-3 py-2.5 font-semibold text-amber-950">
                                            <span>⌂</span> Overview
                                        </div>
                                        <div class="flex items-center gap-2 rounded-lg px-3 py-2.5 text-stone-500">
                                            <span>☑</span> My Tasks
                                            <span class="ml-auto rounded-full bg-stone-100 px-1.5 text-[10px]">8</span>
                                        </div>
                                        <div class="flex items-center gap-2 rounded-lg px-3 py-2.5 text-stone-500">
                                            <span>▦</span> Calendar
                                        </div>
                                        <div class="flex items-center gap-2 rounded-lg px-3 py-2.5 text-stone-500">
                                            <span>↗</span> Upcoming
                                        </div>
                                    </div>

                                    <p class="mt-8 px-3 text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Projects</p>
                                    <div class="mt-3 space-y-3 px-3 text-xs text-stone-600">
                                        <div class="flex items-center gap-2"><span class="h-2 w-2 rounded-full bg-violet-500"></span> Website launch</div>
                                        <div class="flex items-center gap-2"><span class="h-2 w-2 rounded-full bg-sky-500"></span> Mobile app</div>
                                        <div class="flex items-center gap-2"><span class="h-2 w-2 rounded-full bg-emerald-500"></span> Q3 planning</div>
                                    </div>
                                </aside>

                                <div class="p-4 sm:p-6 lg:p-8">
                                    <div class="flex items-start justify-between gap-4">
                                        <div>
                                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-stone-400">Wednesday, July 29</p>
                                            <h2 class="mt-1 text-2xl font-bold tracking-tight text-stone-950 sm:text-3xl">Good morning, Maya.</h2>
                                            <p class="mt-1 text-sm text-stone-500">You have 4 tasks ready for today.</p>
                                        </div>
                                        <span class="hidden rounded-lg bg-stone-950 px-3 py-2 text-xs font-semibold text-white md:block">+ New task</span>
                                    </div>

                                    <div class="mt-7 grid gap-3 md:grid-cols-3">
                                        <div class="rounded-xl border border-stone-200 bg-white p-4 shadow-sm" data-preview-item style="--preview-delay: 100ms">
                                            <p class="text-xs font-medium text-stone-500">Tasks completed</p>
                                            <div class="mt-2 flex items-end justify-between">
                                                <span class="text-2xl font-bold">12</span>
                                                <span class="text-xs font-semibold text-emerald-600">+18%</span>
                                            </div>
                                        </div>
                                        <div class="rounded-xl border border-stone-200 bg-white p-4 shadow-sm" data-preview-item style="--preview-delay: 170ms">
                                            <p class="text-xs font-medium text-stone-500">Daily progress</p>
                                            <div class="mt-2 flex items-center gap-3">
                                                <span class="text-2xl font-bold">72%</span>
                                                <span class="h-2 flex-1 overflow-hidden rounded-full bg-stone-100">
                                                    <span class="landing-progress-fill block h-full w-[72%] origin-left rounded-full bg-amber-400"></span>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="rounded-xl border border-stone-200 bg-white p-4 shadow-sm" data-preview-item style="--preview-delay: 240ms">
                                            <p class="text-xs font-medium text-stone-500">Upcoming</p>
                                            <div class="mt-2 flex items-end justify-between">
                                                <span class="text-2xl font-bold">7</span>
                                                <span class="text-xs text-stone-400">this week</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-5 grid gap-5 lg:grid-cols-[1.35fr_0.65fr]">
                                        <div class="rounded-xl border border-stone-200 bg-white p-4 shadow-sm" data-preview-item style="--preview-delay: 290ms">
                                            <div class="flex items-center justify-between">
                                                <h3 class="text-sm font-bold">Today</h3>
                                                <span class="text-xs text-stone-400">4 tasks</span>
                                            </div>

                                            <div class="mt-3 divide-y divide-stone-100">
                                                @foreach ([
                                                    ['Review launch copy', 'Website launch', 'High', 'bg-red-400'],
                                                    ['Approve mobile prototype', 'Mobile app', 'Medium', 'bg-amber-400'],
                                                    ['Prepare sprint notes', 'Q3 planning', 'Low', 'bg-stone-400'],
                                                    ['Schedule team retro', 'Mobile app', 'Medium', 'bg-amber-400'],
                                                ] as [$task, $project, $priority, $color])
                                                    <div
                                                        class="flex items-center gap-3 py-3"
                                                        data-preview-item
                                                        style="--preview-delay: {{ 360 + ($loop->index * 55) }}ms"
                                                    >
                                                        <span class="h-4 w-4 shrink-0 rounded-full border-2 border-stone-300"></span>
                                                        <div class="min-w-0 flex-1">
                                                            <p class="truncate text-xs font-semibold text-stone-800 sm:text-sm">{{ $task }}</p>
                                                            <p class="mt-0.5 truncate text-[11px] text-stone-400">{{ $project }}</p>
                                                        </div>
                                                        <span class="hidden items-center gap-1.5 text-[10px] font-medium text-stone-500 sm:flex">
                                                            <span class="h-1.5 w-1.5 rounded-full {{ $color }}"></span>
                                                            {{ $priority }}
                                                        </span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>

                                        <div class="rounded-xl border border-stone-200 bg-white p-4 shadow-sm" data-preview-item style="--preview-delay: 400ms">
                                            <div class="flex items-center justify-between">
                                                <h3 class="text-sm font-bold">July</h3>
                                                <span class="text-xs text-stone-400">2026</span>
                                            </div>
                                            <div class="mt-4 grid grid-cols-7 gap-y-3 text-center text-[10px]">
                                                @foreach (['M', 'T', 'W', 'T', 'F', 'S', 'S'] as $day)
                                                    <span class="font-bold text-stone-400">{{ $day }}</span>
                                                @endforeach
                                                @foreach (range(27, 31) as $date)
                                                    <span class="text-stone-300">{{ $date }}</span>
                                                @endforeach
                                                @foreach (range(1, 26) as $date)
                                                    <span @class([
                                                        'mx-auto flex h-5 w-5 items-center justify-center rounded-full',
                                                        'bg-stone-950 font-bold text-white' => $date === 15,
                                                        'text-stone-600' => $date !== 15,
                                                    ])>{{ $date }}</span>
                                                @endforeach
                                            </div>
                                            <div class="mt-5 rounded-lg bg-amber-50 p-3">
                                                <p class="text-[10px] font-bold uppercase tracking-wide text-amber-700">Next deadline</p>
                                                <p class="mt-1 text-xs font-semibold text-stone-800">Launch review</p>
                                                <p class="mt-0.5 text-[10px] text-stone-500">Tomorrow, 10:00</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section id="features" class="scroll-mt-8 border-y border-stone-200 bg-white py-20 sm:py-28">
                    <div class="mx-auto max-w-7xl px-5 sm:px-8 lg:px-10">
                        <div class="max-w-2xl" data-reveal>
                            <p class="text-sm font-bold uppercase tracking-[0.18em] text-amber-600">Everything in one flow</p>
                            <h2 class="mt-3 text-3xl font-bold tracking-tight text-stone-950 sm:text-4xl">
                                Organized enough for teams.<br class="hidden sm:block"> Simple enough for every day.
                            </h2>
                            <p class="mt-5 text-lg leading-8 text-stone-600">
                                From a quick personal task to a shared product launch, Taskku keeps the details visible and the next step clear.
                            </p>
                        </div>

                        <div class="mt-12 grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                            @foreach ([
                                ['01', 'Workspaces that stay separate', 'Keep teams, clients, and personal plans neatly isolated with workspace-aware access.'],
                                ['02', 'Projects with real momentum', 'Turn big outcomes into assigned, prioritized tasks with clear due dates and progress.'],
                                ['03', 'A calendar you can act on', 'See the month at a glance, spot high-priority work, and jump straight into any task.'],
                                ['04', 'Reminders that arrive on time', 'Database notifications keep assignments and approaching deadlines from slipping by.'],
                                ['05', 'A forgiving workflow', 'Complete, reopen, archive, trash, and restore work without losing important context.'],
                                ['06', 'Your view, your way', 'Choose light or dark mode and compact or comfortable density for the pace you prefer.'],
                            ] as [$number, $title, $description])
                                <article
                                    class="group rounded-2xl border border-stone-200 bg-stone-50/60 p-6 transition hover:-translate-y-1 hover:border-amber-300 hover:bg-amber-50/40 hover:shadow-lg hover:shadow-amber-900/5"
                                    data-reveal
                                    style="--reveal-delay: {{ ($loop->index % 3) * 80 }}ms"
                                >
                                    <span class="text-xs font-bold tracking-[0.18em] text-amber-600">{{ $number }}</span>
                                    <h3 class="mt-6 text-lg font-bold tracking-tight text-stone-950">{{ $title }}</h3>
                                    <p class="mt-3 text-sm leading-6 text-stone-600">{{ $description }}</p>
                                </article>
                            @endforeach
                        </div>
                    </div>
                </section>

                <section class="px-5 py-20 sm:px-8 sm:py-28 lg:px-10">
                    <div
                        class="landing-final-cta mx-auto max-w-5xl overflow-hidden rounded-3xl bg-stone-950 px-6 py-12 text-center text-white shadow-2xl sm:px-12 sm:py-16"
                        data-reveal
                        data-reveal-distance="large"
                    >
                        <div class="landing-cta-icon mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-400 text-2xl font-bold text-amber-950">✓</div>
                        <h2 class="mx-auto mt-6 max-w-2xl text-3xl font-bold tracking-tight sm:text-4xl">
                            Your best work starts with a clear next step.
                        </h2>
                        <p class="mx-auto mt-4 max-w-xl text-base leading-7 text-stone-400">
                            Create your first workspace, bring every task into focus, and let Taskku handle the rest.
                        </p>
                        <a
                            href="{{ url('/app/register') }}"
                            class="mt-8 inline-flex items-center gap-2 rounded-xl bg-amber-400 px-6 py-3.5 text-base font-bold text-amber-950 transition hover:-translate-y-0.5 hover:bg-amber-300 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-amber-400"
                        >
                            Get Started for Free
                            <span class="landing-arrow" aria-hidden="true">→</span>
                        </a>
                    </div>
                </section>
            </main>

            <footer class="border-t border-stone-200 bg-white">
                <div class="mx-auto flex max-w-7xl flex-col gap-5 px-5 py-8 text-sm text-stone-500 sm:flex-row sm:items-center sm:justify-between sm:px-8 lg:px-10">
                    <a href="{{ route('home') }}" class="inline-flex items-center gap-2 font-bold text-stone-900">
                        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-stone-950 text-xs text-amber-300">✓</span>
                        Taskku
                    </a>
                    <p>&copy; {{ now()->year }} Taskku. Make progress, calmly.</p>
                    <div class="flex gap-5 font-medium">
                        <a href="{{ url('/app/login') }}" class="transition hover:text-stone-950">Sign In</a>
                        <a href="{{ url('/app/register') }}" class="transition hover:text-stone-950">Create Account</a>
                    </div>
                </div>
            </footer>
        </div>

        <div class="landing-page-transition" aria-hidden="true" data-page-transition>
            <div class="landing-transition-content">
                <span class="landing-transition-mark">✓</span>
                <span data-transition-label>Preparing Taskku</span>
            </div>
        </div>
    </body>
</html>
