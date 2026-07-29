<x-filament-panels::page>
    <div class="space-y-5">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Tasks scheduled by due date</p>
                <h2 class="text-2xl font-semibold tracking-tight text-gray-950 dark:text-white">
                    {{ $monthLabel }}
                </h2>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <x-filament::icon-button
                    color="gray"
                    icon="heroicon-m-chevron-left"
                    label="Previous month"
                    wire:click="previousMonth"
                    wire:loading.attr="disabled"
                />

                <x-filament::button
                    color="gray"
                    wire:click="goToToday"
                    wire:loading.attr="disabled"
                >
                    Today
                </x-filament::button>

                <x-filament::icon-button
                    color="gray"
                    icon="heroicon-m-chevron-right"
                    label="Next month"
                    wire:click="nextMonth"
                    wire:loading.attr="disabled"
                />
            </div>
        </div>

        <div class="overflow-x-auto rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="min-w-[56rem]">
                <div class="grid grid-cols-7 border-b border-gray-200 bg-gray-50 dark:border-white/10 dark:bg-gray-950">
                    @foreach ($weekdays as $weekday)
                        <div class="px-3 py-2 text-center text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            {{ $weekday }}
                        </div>
                    @endforeach
                </div>

                <div class="grid grid-cols-7">
                    @foreach ($calendarDays as $day)
                        <div
                            wire:key="calendar-day-{{ $day['date'] }}"
                            @class([
                                'min-h-36 border-b border-r border-gray-200 p-2 dark:border-white/10',
                                'bg-gray-50/70 dark:bg-gray-950/50' => ! $day['isCurrentMonth'],
                                'bg-white dark:bg-gray-900' => $day['isCurrentMonth'],
                            ])
                        >
                            <div class="mb-2 flex items-center justify-between">
                                <time
                                    datetime="{{ $day['date'] }}"
                                    @class([
                                        'flex h-7 w-7 items-center justify-center rounded-full text-sm font-medium',
                                        'bg-primary-600 text-white' => $day['isToday'],
                                        'text-gray-950 dark:text-white' => $day['isCurrentMonth'] && ! $day['isToday'],
                                        'text-gray-400 dark:text-gray-600' => ! $day['isCurrentMonth'] && ! $day['isToday'],
                                    ])
                                >
                                    {{ $day['day'] }}
                                </time>
                            </div>

                            <div class="space-y-1.5">
                                @foreach ($day['events'] as $event)
                                    <a
                                        href="{{ $event['url'] }}"
                                        title="{{ $event['title'] }} — {{ $event['priority'] }}"
                                        class="group block rounded-md border-l-4 px-2 py-1.5 transition {{ $event['colorClasses'] }}"
                                    >
                                        <div class="flex items-start gap-1.5">
                                            <span class="shrink-0 text-[11px] font-medium text-gray-500 dark:text-gray-400">
                                                {{ $event['time'] }}
                                            </span>
                                            <span class="line-clamp-2 text-xs font-semibold text-gray-900 group-hover:underline dark:text-white">
                                                {{ $event['title'] }}
                                            </span>
                                        </div>

                                        @if ($event['project'])
                                            <p class="mt-0.5 truncate text-[11px] text-gray-500 dark:text-gray-400">
                                                {{ $event['project'] }}
                                            </p>
                                        @endif
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="flex flex-wrap gap-x-4 gap-y-2 text-xs text-gray-500 dark:text-gray-400">
            <span class="inline-flex items-center gap-1.5">
                <span class="h-2.5 w-2.5 rounded-full bg-gray-400"></span>
                Low
            </span>
            <span class="inline-flex items-center gap-1.5">
                <span class="h-2.5 w-2.5 rounded-full bg-amber-500"></span>
                Medium
            </span>
            <span class="inline-flex items-center gap-1.5">
                <span class="h-2.5 w-2.5 rounded-full bg-red-500"></span>
                High
            </span>
        </div>
    </div>
</x-filament-panels::page>
