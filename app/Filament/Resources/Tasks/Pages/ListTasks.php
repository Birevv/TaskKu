<?php

namespace App\Filament\Resources\Tasks\Pages;

use App\Enums\TaskStatus;
use App\Filament\Resources\Tasks\TaskResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListTasks extends ListRecords
{
    protected static string $resource = TaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('New Task'),
        ];
    }

    public function getTabs(): array
    {
        $todayEndUtc = now('Asia/Jakarta')
            ->endOfDay()
            ->utc();

        return [
            'inbox' => Tab::make('Inbox')
                ->icon('heroicon-o-inbox')
                ->modifyQueryUsing(
                    fn (Builder $query): Builder => $query->active()
                ),

            'today' => Tab::make('Today')
                ->icon('heroicon-o-calendar')
                ->modifyQueryUsing(
                    fn (Builder $query): Builder => $query
                        ->active()
                        ->whereNotNull('due_at')
                        ->where('due_at', '<=', $todayEndUtc)
                ),

            'upcoming' => Tab::make('Upcoming')
                ->icon('heroicon-o-calendar-days')
                ->modifyQueryUsing(
                    fn (Builder $query): Builder => $query
                        ->active()
                        ->where('due_at', '>', $todayEndUtc)
                ),

            'completed' => Tab::make('Completed')
                ->icon('heroicon-o-check-circle')
                ->modifyQueryUsing(
                    fn (Builder $query): Builder => $query
                        ->where('status', TaskStatus::Completed)
                        ->whereNull('archived_at')
                ),

            'archived' => Tab::make('Archived')
                ->icon('heroicon-o-archive-box')
                ->modifyQueryUsing(
                    fn (Builder $query): Builder => $query->whereNotNull('archived_at')
                ),

            'trash' => Tab::make('Trash')
                ->icon('heroicon-o-trash')
                ->modifyQueryUsing(
                    fn (Builder $query): Builder => $query->onlyTrashed()
                ),

            'all' => Tab::make('All'),
        ];
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return 'inbox';
    }
}
