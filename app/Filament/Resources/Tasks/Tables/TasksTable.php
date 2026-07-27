<?php

namespace App\Filament\Resources\Tasks\Tables;

use App\Actions\Task\ChangeTaskStatusAction;
use App\Enums\TaskStatus;
use App\Models\Task;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

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
                    ->relationship('project', 'name')
                    ->searchable()
                    ->preload(),

                TrashedFilter::make(),
            ])

            ->recordActions([
                Action::make('complete')
                    ->label('Complete')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Task $record): bool =>
                    $record->status !== TaskStatus::Completed
                    && $record->deleted_at === null
                    )
                    ->action(fn (Task $record): Task => app(ChangeTaskStatusAction::class)
                        ->handle($record, TaskStatus::Completed)
                    ),
                Action::make('reopen')
                    ->label('Reopen')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->visible(fn (Task $record): bool =>
                        $record->status === TaskStatus::Completed
                        && $record->deleted_at === null
                    )
                    ->action(fn (Task $record): Task =>
                        app(ChangeTaskStatusAction::class)
                            ->handle($record, TaskStatus::Pending)
                    ),

                    EditAction::make(),
                    DeleteAction::make(),
                    RestoreAction::make(),
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
