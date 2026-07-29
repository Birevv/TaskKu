<x-filament-panels::page class="taskku-dashboard-page">
    <div class="taskku-dashboard">
        <section class="taskku-dashboard-hero">
            <div>
                <p class="taskku-eyebrow">{{ $dateLabel }}</p>
                <h1>{{ $greeting }}, {{ $userFirstName }}.</h1>
                <p class="taskku-hero-copy">
                    You have {{ $readyToday }} {{ \Illuminate\Support\Str::plural('task', $readyToday) }} ready for today.
                </p>
            </div>

            <a href="{{ $createTaskUrl }}" class="taskku-new-task-button">
                <x-filament::icon icon="heroicon-m-plus" />
                <span>New task</span>
            </a>
        </section>

        <section class="taskku-stats-grid" aria-label="Task summary">
            <article class="taskku-stat-card">
                <p>Tasks completed</p>
                <div class="taskku-stat-value-row">
                    <strong>{{ $completedTotal }}</strong>
                    <span @class([
                        'taskku-trend',
                        'is-down' => $completionTrend < 0,
                    ])>
                        {{ $completionTrend > 0 ? '+' : '' }}{{ $completionTrend }}%
                    </span>
                </div>
            </article>

            <article class="taskku-stat-card">
                <p>Daily progress</p>
                <div class="taskku-progress-row">
                    <strong>{{ $dailyProgress }}%</strong>
                    <div
                        class="taskku-progress-track"
                        role="progressbar"
                        aria-label="Daily progress"
                        aria-valuemin="0"
                        aria-valuemax="100"
                        aria-valuenow="{{ $dailyProgress }}"
                    >
                        <span style="width: {{ $dailyProgress }}%"></span>
                    </div>
                </div>
                <span class="taskku-stat-caption">{{ $todayCompleted }} of {{ $todayTotal }} done</span>
            </article>

            <article class="taskku-stat-card">
                <p>Upcoming</p>
                <div class="taskku-stat-value-row">
                    <strong>{{ $upcomingCount }}</strong>
                    <span class="taskku-stat-caption">next 7 days</span>
                </div>
            </article>
        </section>

        <section class="taskku-dashboard-grid">
            <article class="taskku-panel taskku-today-panel">
                <header class="taskku-panel-header">
                    <h2>Today</h2>
                    <span>{{ $todayTotal }} {{ \Illuminate\Support\Str::plural('task', $todayTotal) }}</span>
                </header>

                <div class="taskku-task-list">
                    @forelse ($todayTasks as $task)
                        @php
                            $isCompleted = $task->status === \App\Enums\TaskStatus::Completed;
                            $priority = $task->priority instanceof \App\Enums\TaskPriority
                                ? $task->priority
                                : \App\Enums\TaskPriority::from($task->priority);
                            $taskUrl = \App\Filament\Resources\Tasks\TaskResource::getUrl(
                                'edit',
                                ['record' => $task],
                                tenant: $workspace,
                            );
                        @endphp

                        <div class="taskku-task-row" wire:key="dashboard-task-{{ $task->getKey() }}">
                            <button
                                type="button"
                                class="taskku-task-check {{ $isCompleted ? 'is-completed' : '' }}"
                                aria-label="{{ $isCompleted ? 'Task completed' : 'Mark '.$task->title.' as completed' }}"
                                @disabled($isCompleted)
                                @if (! $isCompleted)
                                    wire:click="completeTask({{ $task->getKey() }})"
                                    wire:loading.attr="disabled"
                                @endif
                            >
                                @if ($isCompleted)
                                    <x-filament::icon icon="heroicon-m-check" />
                                @endif
                            </button>

                            <a href="{{ $taskUrl }}" class="taskku-task-copy">
                                <strong class="{{ $isCompleted ? 'is-completed' : '' }}">{{ $task->title }}</strong>
                                <span>{{ $task->project?->name ?? 'Personal' }}</span>
                            </a>

                            <span class="taskku-priority taskku-priority-{{ $priority->value }}">
                                <i></i>
                                {{ $priority->getLabel() }}
                            </span>
                        </div>
                    @empty
                        <div class="taskku-empty-state">
                            <span><x-filament::icon icon="heroicon-o-sparkles" /></span>
                            <h3>Your day is clear</h3>
                            <p>Add a task for today or enjoy the extra breathing room.</p>
                            <a href="{{ $createTaskUrl }}">Plan a task</a>
                        </div>
                    @endforelse
                </div>
            </article>

            <aside class="taskku-panel taskku-calendar-panel">
                <header class="taskku-panel-header">
                    <a href="{{ $calendarUrl }}">{{ $monthLabel }}</a>
                    <span>{{ $yearLabel }}</span>
                </header>

                <div class="taskku-mini-calendar" aria-label="{{ $monthLabel }} {{ $yearLabel }}">
                    @foreach (['M', 'T', 'W', 'T', 'F', 'S', 'S'] as $weekday)
                        <span class="taskku-weekday">{{ $weekday }}</span>
                    @endforeach

                    @foreach ($calendarDays as $day)
                        <time
                            datetime="{{ $day['date'] }}"
                            @class([
                                'taskku-calendar-day',
                                'is-muted' => ! $day['isCurrentMonth'],
                                'is-today' => $day['isToday'],
                                'has-tasks' => $day['hasTasks'],
                            ])
                        >
                            {{ $day['day'] }}
                        </time>
                    @endforeach
                </div>

                @if ($nextDeadline)
                    <a href="{{ $nextDeadlineUrl }}" class="taskku-deadline-card">
                        <span>Next deadline</span>
                        <strong>{{ $nextDeadline->title }}</strong>
                        <small>
                            {{ $nextDeadline->due_at->timezone($today->timezone)->isToday() ? 'Today' : $nextDeadline->due_at->timezone($today->timezone)->format('D, M j') }},
                            {{ $nextDeadline->due_at->timezone($today->timezone)->format('H:i') }}
                        </small>
                    </a>
                @else
                    <a href="{{ $createTaskUrl }}" class="taskku-deadline-card is-empty">
                        <span>Next deadline</span>
                        <strong>Nothing scheduled</strong>
                        <small>Add a due date to stay ahead.</small>
                    </a>
                @endif
            </aside>
        </section>
    </div>
</x-filament-panels::page>
