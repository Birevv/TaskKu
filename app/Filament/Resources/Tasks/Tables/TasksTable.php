<?php

namespace App\Filament\Resources\Tasks\Tables;

use App\Actions\Task\ArchiveTaskAction;
use App\Actions\Task\ChangeTaskStatusAction;
use App\Actions\Task\UnarchiveTaskAction;
use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\Workspace;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Facades\Filament;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TasksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Task')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('project.name')
                    ->label('Project')
                    ->placeholder('Inbox')
                    ->sortable(),

                TextColumn::make('priority')
                    ->badge()
                    ->sortable(),

                TextColumn::make('status')
                    ->badge()
                    ->sortable(),

                TextColumn::make('assginees.name')
                    ->label('Assignees')
                    ->badge(),

                TextColumn::make('due_at')
                    ->label('Due Date')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->placeholder('No deadline'),
            ])
            ->filters([
                SelectFilter::make('priority')
                    ->options([
                        'low' => 'Low',
                        'medium' => 'Medium',
                        'high' => 'High',
                    ]),

                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'in_progress' => 'In progress',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ]),

                SelectFilter::make('project')
                    ->relationship(
                        'project',
                        'name',
                        function (Builder $query): Builder {
                            $workspace = Filament::getTenant();

                            return $workspace instanceof Workspace
                                ? $query->whereBelongsTo($workspace)
                                : $query->whereRaw('1 = 0');
                        },
                    )
                    ->searchable()
                    ->preload(),
            ])

            ->recordActions([
                Action::make('complete')
                    ->label('Complete')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Task $record): bool => $record->status !== TaskStatus::Completed
                    && $record->deleted_at === null
                    )
                    ->action(fn (Task $record): Task => app(ChangeTaskStatusAction::class)
                        ->handle($record, TaskStatus::Completed)
                    ),
                Action::make('reopen')
                    ->label('Reopen')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->visible(fn (Task $record): bool => $record->status === TaskStatus::Completed
                        && $record->deleted_at === null
                    )
                    ->action(fn (Task $record): Task => app(ChangeTaskStatusAction::class)
                        ->handle($record, TaskStatus::Pending)
                    ),

                Action::make('archive')
                    ->label('Archive')
                    ->icon('heroicon-o-archive-box')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(
                        fn (Task $record): bool => is_null($record->archived_at)
                    )
                    ->action(function (Task $record): void {
                        app(ArchiveTaskAction::class)->handle($record);
                    }),

                Action::make('unarchive')
                    ->label('Unarchive')
                    ->icon('heroicon-o-archive-box-x-mark')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(
                        fn (Task $record): bool => $record->archived_at !== null
                    )
                    ->action(function (Task $record): void {
                        app(UnarchiveTaskAction::class)->handle($record);
                    }),

                EditAction::make(),
                DeleteAction::make(),
                RestoreAction::make(),
                ForceDeleteAction::make()
                    ->label('Delete permanently')
                    ->requiresConfirmation(),
            ])

            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ])

            ->defaultSort('due_at');
    }
}
