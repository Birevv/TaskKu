<?php

namespace App\Filament;

use App\Actions\Task\ChangeTaskStatusAction;
use App\Enums\TaskStatus;
use App\Filament\Pages\Calendar;
use App\Filament\Resources\Tasks\TaskResource;
use App\Models\Task;
use App\Models\User;
use App\Models\Workspace;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Support\Enums\Width;
use Filament\Widgets\Widget;
use Filament\Widgets\WidgetConfiguration;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class Dashboard extends BaseDashboard
{
    protected string $view = 'filament.pages.dashboard';

    protected static ?string $navigationLabel = 'Overview';

    protected static ?int $navigationSort = 1;

    protected static ?string $title = 'Overview';

    public function getHeading(): string|Htmlable|null
    {
        return null;
    }

    public function getMaxContentWidth(): Width|string|null
    {
        return Width::Full;
    }

    /**
     * @return array<class-string<Widget> | WidgetConfiguration>
     */
    public function getWidgets(): array
    {
        return [];
    }

    public function completeTask(int $taskId): void
    {
        $workspace = Filament::getTenant();

        abort_unless($workspace instanceof Workspace, 404);

        $task = Task::query()
            ->whereBelongsTo($workspace)
            ->whereNull('archived_at')
            ->findOrFail($taskId);

        Gate::authorize('update', $task);

        app(ChangeTaskStatusAction::class)->handle($task, TaskStatus::Completed);

        Notification::make()
            ->title('Task completed')
            ->body($task->title)
            ->success()
            ->send();
    }

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $workspace = Filament::getTenant();
        $user = Auth::user();
        $timezone = $this->getUserTimezone($user);
        $today = CarbonImmutable::now($timezone);

        if (! $workspace instanceof Workspace) {
            return $this->emptyViewData($user, $today);
        }

        $dayStartUtc = $today->startOfDay()->utc();
        $dayEndUtc = $today->endOfDay()->utc();

        $todayTasks = Task::query()
            ->leftJoin('projects', function (JoinClause $join): void {
                $join
                    ->on('projects.id', '=', 'tasks.project_id')
                    ->whereNull('projects.deleted_at');
            })
            ->where('tasks.workspace_id', $workspace->getKey())
            ->whereNull('tasks.archived_at')
            ->where('tasks.status', '!=', TaskStatus::Cancelled)
            ->whereBetween('tasks.due_at', [$dayStartUtc, $dayEndUtc])
            ->select(['tasks.*', 'projects.name as project_name'])
            ->orderByRaw("CASE tasks.priority WHEN 'high' THEN 1 WHEN 'medium' THEN 2 ELSE 3 END")
            ->orderBy('tasks.due_at')
            ->get();

        $todayTotal = $todayTasks->count();
        $todayCompleted = $todayTasks
            ->filter(fn (Task $task): bool => $task->status === TaskStatus::Completed)
            ->count();
        $readyToday = $todayTasks
            ->filter(fn (Task $task): bool => ! in_array($task->status, [TaskStatus::Completed, TaskStatus::Cancelled], true))
            ->count();
        $dailyProgress = $todayTotal === 0
            ? 0
            : (int) round(($todayCompleted / $todayTotal) * 100);

        $weekStart = $today->startOfWeek(CarbonInterface::MONDAY);
        $weekEnd = $today->endOfWeek(CarbonInterface::SUNDAY);
        $previousWeekStart = $weekStart->subWeek();
        $previousWeekEnd = $weekEnd->subWeek();
        $upcomingStart = $today->addDay()->startOfDay()->utc();
        $upcomingEnd = $today->addDays(7)->endOfDay()->utc();
        $stats = $this->getTaskStats(
            $workspace,
            $weekStart,
            $weekEnd,
            $previousWeekStart,
            $previousWeekEnd,
            $upcomingStart,
            $upcomingEnd,
        );
        $completedTotal = $stats['completedTotal'];
        $completedThisWeek = $stats['completedThisWeek'];
        $completedPreviousWeek = $stats['completedPreviousWeek'];
        $completionTrend = $completedPreviousWeek === 0
            ? ($completedThisWeek > 0 ? 100 : 0)
            : (int) round((($completedThisWeek - $completedPreviousWeek) / $completedPreviousWeek) * 100);

        $nextDeadline = Task::query()
            ->active()
            ->whereBelongsTo($workspace)
            ->whereNotNull('due_at')
            ->where('due_at', '>=', now())
            ->orderBy('due_at')
            ->first();

        return [
            'calendarDays' => $this->getCalendarDays($workspace, $today),
            'calendarUrl' => Calendar::getUrl(tenant: $workspace),
            'completedTotal' => $completedTotal,
            'completionTrend' => $completionTrend,
            'createTaskUrl' => TaskResource::getUrl('create', tenant: $workspace),
            'dailyProgress' => $dailyProgress,
            'dateLabel' => $today->format('l, F j'),
            'greeting' => $this->getGreeting($today),
            'monthLabel' => $today->format('F'),
            'nextDeadline' => $nextDeadline,
            'nextDeadlineUrl' => $nextDeadline
                ? TaskResource::getUrl('edit', ['record' => $nextDeadline], tenant: $workspace)
                : null,
            'readyToday' => $readyToday,
            'today' => $today,
            'todayCompleted' => $todayCompleted,
            'todayTasks' => $todayTasks,
            'todayTotal' => $todayTotal,
            'upcomingCount' => $stats['upcomingCount'],
            'userFirstName' => str((string) ($user?->name ?? 'there'))->before(' ')->toString(),
            'workspace' => $workspace,
            'yearLabel' => $today->format('Y'),
        ];
    }

    /**
     * @return array{
     *     completedTotal: int,
     *     completedThisWeek: int,
     *     completedPreviousWeek: int,
     *     upcomingCount: int
     * }
     */
    private function getTaskStats(
        Workspace $workspace,
        CarbonImmutable $weekStart,
        CarbonImmutable $weekEnd,
        CarbonImmutable $previousWeekStart,
        CarbonImmutable $previousWeekEnd,
        CarbonImmutable $upcomingStart,
        CarbonImmutable $upcomingEnd,
    ): array {
        $stats = Task::query()
            ->whereBelongsTo($workspace)
            ->whereNull('archived_at')
            ->selectRaw(
                <<<'SQL'
                    COALESCE(SUM(CASE WHEN status = ? THEN 1 ELSE 0 END), 0) AS completed_total,
                    COALESCE(SUM(CASE WHEN status = ? AND completed_at BETWEEN ? AND ? THEN 1 ELSE 0 END), 0) AS completed_this_week,
                    COALESCE(SUM(CASE WHEN status = ? AND completed_at BETWEEN ? AND ? THEN 1 ELSE 0 END), 0) AS completed_previous_week,
                    COALESCE(SUM(CASE WHEN status IN (?, ?) AND due_at BETWEEN ? AND ? THEN 1 ELSE 0 END), 0) AS upcoming_count
                SQL,
                [
                    TaskStatus::Completed->value,
                    TaskStatus::Completed->value,
                    $weekStart->utc(),
                    $weekEnd->utc(),
                    TaskStatus::Completed->value,
                    $previousWeekStart->utc(),
                    $previousWeekEnd->utc(),
                    TaskStatus::Pending->value,
                    TaskStatus::InProgress->value,
                    $upcomingStart,
                    $upcomingEnd,
                ],
            )
            ->first();

        return [
            'completedTotal' => (int) ($stats?->completed_total ?? 0),
            'completedThisWeek' => (int) ($stats?->completed_this_week ?? 0),
            'completedPreviousWeek' => (int) ($stats?->completed_previous_week ?? 0),
            'upcomingCount' => (int) ($stats?->upcoming_count ?? 0),
        ];
    }

    /**
     * @return array<int, array{
     *     date: string,
     *     day: int,
     *     isCurrentMonth: bool,
     *     isToday: bool,
     *     hasTasks: bool
     * }>
     */
    private function getCalendarDays(Workspace $workspace, CarbonImmutable $today): array
    {
        $monthStart = $today->startOfMonth();
        $gridStart = $monthStart->startOfWeek(CarbonInterface::MONDAY);
        $gridEnd = $monthStart->endOfMonth()->endOfWeek(CarbonInterface::SUNDAY);
        $taskDates = Task::query()
            ->whereBelongsTo($workspace)
            ->whereNull('archived_at')
            ->whereNotNull('due_at')
            ->whereBetween('due_at', [$gridStart->utc(), $gridEnd->endOfDay()->utc()])
            ->get(['due_at'])
            ->map(fn (Task $task): string => $task->due_at->timezone($today->timezone)->toDateString())
            ->flip();

        $days = [];

        for ($date = $gridStart; $date->lessThanOrEqualTo($gridEnd); $date = $date->addDay()) {
            $dateString = $date->toDateString();

            $days[] = [
                'date' => $dateString,
                'day' => $date->day,
                'isCurrentMonth' => $date->month === $today->month,
                'isToday' => $dateString === $today->toDateString(),
                'hasTasks' => $taskDates->has($dateString),
            ];
        }

        return $days;
    }

    private function getGreeting(CarbonImmutable $today): string
    {
        return match (true) {
            $today->hour < 12 => 'Good morning',
            $today->hour < 18 => 'Good afternoon',
            default => 'Good evening',
        };
    }

    private function getUserTimezone(?User $user): string
    {
        $timezone = $user?->settings?->timezone ?? config('app.timezone');

        return in_array($timezone, timezone_identifiers_list(), true)
            ? $timezone
            : config('app.timezone');
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyViewData(?User $user, CarbonImmutable $today): array
    {
        return [
            'calendarDays' => [],
            'calendarUrl' => '#',
            'completedTotal' => 0,
            'completionTrend' => 0,
            'createTaskUrl' => '#',
            'dailyProgress' => 0,
            'dateLabel' => $today->format('l, F j'),
            'greeting' => $this->getGreeting($today),
            'monthLabel' => $today->format('F'),
            'nextDeadline' => null,
            'nextDeadlineUrl' => null,
            'readyToday' => 0,
            'today' => $today,
            'todayCompleted' => 0,
            'todayTasks' => collect(),
            'todayTotal' => 0,
            'upcomingCount' => 0,
            'userFirstName' => str((string) ($user?->name ?? 'there'))->before(' ')->toString(),
            'workspace' => null,
            'yearLabel' => $today->format('Y'),
        ];
    }
}
