<?php

namespace App\Filament\Widgets;

use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\Workspace;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DailyProgress extends BaseWidget
{
    protected ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $workspace = Filament::getTenant();

        if (! $workspace instanceof Workspace) {
            return [];
        }

        $startUtc = now('Asia/Jakarta')->startOfDay()->utc();
        $endUtc = now('Asia/Jakarta')->endOfDay()->utc();

        $todayTasks = Task::query()
            ->whereBelongsTo($workspace)
            ->whereNull('archived_at')
            ->whereBetween('due_at', [$startUtc, $endUtc]);

        $total = (clone $todayTasks)->count();

        $completed = (clone $todayTasks)
            ->where('status', TaskStatus::Completed)
            ->count();

        $pending = max($total - $completed, 0);

        $percentage = $total === 0
            ? 0
            : (int) round(($completed / $total) * 100);

        return [
            Stat::make('Daily Progress', "{$percentage}%")
                ->description("{$completed} dari {$total} task selesai")
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color($percentage === 0 ? 'success' : 'primary'),

            Stat::make('Completed', $completed)
                ->description('Task selesai hari ini')
                ->color('success'),

            Stat::make('Pending', $pending)
                ->description('Task belum selesai')
                ->color($pending > 0 ? 'warning' : 'gray'),
        ];
    }
}
