<?php

namespace App\Filament\Pages;

use App\Enums\TaskPriority;
use App\Filament\Resources\Tasks\TaskResource;
use App\Models\Task;
use App\Models\User;
use App\Models\Workspace;
use BackedEnum;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Filament\Facades\Filament;
use Filament\Pages\Page;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\Auth;

class Calendar extends Page
{
    protected string $view = 'filament.pages.calendar';

    protected static ?string $navigationLabel = 'Calendar';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-calendar';

    protected static ?int $navigationSort = 4;

    public int $year;

    public int $month;

    public function mount(): void
    {
        $today = CarbonImmutable::now($this->getUserTimezone());

        $this->year = $today->year;
        $this->month = $today->month;
    }

    public function previousMonth(): void
    {
        $month = $this->getSelectedMonth()->subMonth();

        $this->year = $month->year;
        $this->month = $month->month;
    }

    public function nextMonth(): void
    {
        $month = $this->getSelectedMonth()->addMonth();

        $this->year = $month->year;
        $this->month = $month->month;
    }

    public function goToToday(): void
    {
        $today = CarbonImmutable::now($this->getUserTimezone());

        $this->year = $today->year;
        $this->month = $today->month;
    }

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $selectedMonth = $this->getSelectedMonth();

        return [
            'calendarDays' => $this->getCalendarDays($selectedMonth),
            'monthLabel' => $selectedMonth->translatedFormat('F Y'),
            'weekdays' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
        ];
    }

    /**
     * @return array<int, array{
     *     date: string,
     *     day: int,
     *     isCurrentMonth: bool,
     *     isToday: bool,
     *     events: array<int, array{
     *         id: int|string,
     *         title: string,
     *         time: string,
     *         project: string|null,
     *         priority: string,
     *         colorClasses: string,
     *         url: string
     *     }>
     * }>
     */
    private function getCalendarDays(CarbonImmutable $selectedMonth): array
    {
        $gridStart = $selectedMonth->startOfMonth()->startOfWeek(CarbonInterface::MONDAY);
        $gridEnd = $selectedMonth->endOfMonth()->endOfWeek(CarbonInterface::SUNDAY);
        $eventsByDate = $this->getEventsByDate($gridStart, $gridEnd);
        $today = CarbonImmutable::now($this->getUserTimezone())->toDateString();
        $days = [];

        for ($date = $gridStart; $date->lessThanOrEqualTo($gridEnd); $date = $date->addDay()) {
            $dateKey = $date->toDateString();

            $days[] = [
                'date' => $dateKey,
                'day' => $date->day,
                'isCurrentMonth' => $date->month === $selectedMonth->month,
                'isToday' => $dateKey === $today,
                'events' => $eventsByDate[$dateKey] ?? [],
            ];
        }

        return $days;
    }

    /**
     * @return array<string, array<int, array{
     *     id: int|string,
     *     title: string,
     *     time: string,
     *     project: string|null,
     *     priority: string,
     *     colorClasses: string,
     *     url: string
     * }>>
     */
    private function getEventsByDate(CarbonImmutable $gridStart, CarbonImmutable $gridEnd): array
    {
        $workspace = Filament::getTenant();

        if (! $workspace instanceof Workspace) {
            return [];
        }

        $timezone = $this->getUserTimezone();
        $events = [];
        $tasks = Task::query()
            ->leftJoin('projects', function (JoinClause $join): void {
                $join
                    ->on('projects.id', '=', 'tasks.project_id')
                    ->whereNull('projects.deleted_at');
            })
            ->where('tasks.workspace_id', $workspace->getKey())
            ->whereNull('tasks.archived_at')
            ->whereNotNull('tasks.due_at')
            ->whereBetween('tasks.due_at', [
                $gridStart->utc(),
                $gridEnd->endOfDay()->utc(),
            ])
            ->select([
                'tasks.id',
                'tasks.title',
                'tasks.priority',
                'tasks.due_at',
                'projects.name as project_name',
            ])
            ->orderBy('tasks.due_at')
            ->get();

        foreach ($tasks as $task) {
            $dueAt = $task->due_at->timezone($timezone);
            $priority = $task->priority instanceof TaskPriority
                ? $task->priority
                : TaskPriority::from($task->priority);

            $events[$dueAt->toDateString()][] = [
                'id' => $task->getKey(),
                'title' => $task->title,
                'time' => $dueAt->format('H:i'),
                'project' => $task->project_name,
                'priority' => $priority->getLabel(),
                'colorClasses' => $this->getPriorityColorClasses($priority),
                'url' => TaskResource::getUrl('edit', ['record' => $task], tenant: $workspace),
            ];
        }

        return $events;
    }

    private function getSelectedMonth(): CarbonImmutable
    {
        return CarbonImmutable::create($this->year, $this->month, 1, 0, 0, 0, $this->getUserTimezone());
    }

    private function getUserTimezone(): string
    {
        $user = Auth::user();
        $timezone = $user instanceof User
            ? ($user->settings?->timezone ?? config('app.timezone'))
            : config('app.timezone');

        return in_array($timezone, timezone_identifiers_list(), true)
            ? $timezone
            : config('app.timezone');
    }

    private function getPriorityColorClasses(TaskPriority $priority): string
    {
        return match ($priority) {
            TaskPriority::Low => 'border-gray-400 bg-gray-50 hover:bg-gray-100 dark:bg-gray-900 dark:hover:bg-gray-800',
            TaskPriority::Medium => 'border-amber-500 bg-amber-50 hover:bg-amber-100 dark:bg-amber-950 dark:hover:bg-amber-900',
            TaskPriority::High => 'border-red-500 bg-red-50 hover:bg-red-100 dark:bg-red-950 dark:hover:bg-red-900',
        };
    }
}
