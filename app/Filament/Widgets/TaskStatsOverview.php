<?php

namespace App\Filament\Widgets;

use App\Models\Task;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class TaskStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $userId = Auth::id();

        $total = Task::query()
            ->where('created_by', $userId)
            ->count();

        $pending = Task::query()
            ->where('created_by', $userId)
            ->where('status', 'pending')
            ->count();

        $completed = Task::query()
            ->where('created_by', $userId)
            ->where('status', 'completed')
            ->count();

        $overdue = Task::query()
            ->where('created_by', $userId)
            ->whereNotNull('due_at')
            ->where('due_at', '<', now())
            ->where('status', '!=', 'completed')
            ->count();

        return [
            Stat::make('Total Tasks', $total)
                ->description('Semua task anda')
                ->icon('heroicon-o-clipboard-document-list'),

            Stat::make('Pending', $pending)
                ->description('Task yang belum selesai')
                ->icon('heroicon-o-clock')
                ->color('warning'),

            Stat::make('Completed', $completed)
                ->description('Task yang sudah selesai')
                ->icon('heroicon-o-check-circle')
                ->color('success'),

            Stat::make('Overdue', $overdue)
                ->description('Melewati tenggat waktu')
                ->icon('heroicon-o-check-circle')
                ->color('danger'),
        ];
    }
}
