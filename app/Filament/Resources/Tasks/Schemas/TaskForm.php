<?php

namespace App\Filament\Resources\Tasks\Schemas;

use App\Enums\TaskPriority;
use App\Models\User;
use App\Models\Workspace;
use Closure;
use Filament\Facades\Filament;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class TaskForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Task details')
                    ->schema([
                        TextInput::make('title')
                            ->label('Task title')
                            ->required()
                            ->maxLength(200)
                            ->columnSpanFull(),

                        MarkdownEditor::make('description')
                            ->label('Description')
                            ->columnSpanFull(),

                        Select::make('project_id')
                            ->label('Project')
                            ->relationship(
                                name: 'project',
                                titleAttribute: 'name',
                                modifyQueryUsing: function (Builder $query): Builder {
                                    $workspace = Filament::getTenant();

                                    return $workspace instanceof Workspace
                                        ? $query
                                            ->whereBelongsTo($workspace)
                                            ->whereNull('archived_at')
                                        : $query->whereRaw('1 = 0');
                                },
                            )
                            ->searchable()
                            ->preload(),

                        Select::make('priority')
                            ->options(TaskPriority::class)
                            ->default(TaskPriority::Medium)
                            ->required(),

                        DateTimePicker::make('due_at')
                            ->label('Due date')
                            ->seconds(false)
                            ->native(false),

                        DateTimePicker::make('reminder_at')
                            ->label('Reminder')
                            ->seconds(false)
                            ->native(false)
                            ->beforeOrEqual('due_at'),

                        Select::make('assignees')
                            ->label('Assignees')
                            ->relationship(
                                name: 'assignees',
                                titleAttribute: 'name',
                                modifyQueryUsing: function (Builder $query): Builder {
                                    $workspace = Filament::getTenant();

                                    return $workspace instanceof Workspace
                                        ? $query->whereHas(
                                            'workspaces',
                                            fn (Builder $query): Builder => $query->whereKey($workspace->getKey()),
                                        )
                                        : $query->whereRaw('1 = 0');
                                },
                            )
                            ->multiple()
                            ->rule(function (): Closure {
                                return function (string $attribute, mixed $value, Closure $fail): void {
                                    if (! is_array($value) || $value === []) {
                                        return;
                                    }

                                    $workspace = Filament::getTenant();
                                    $assigneeIds = array_values(array_unique($value));

                                    if (
                                        ! $workspace instanceof Workspace
                                        || User::query()
                                            ->whereKey($assigneeIds)
                                            ->whereHas(
                                                'workspaces',
                                                fn (Builder $query): Builder => $query->whereKey($workspace->getKey()),
                                            )
                                            ->count() !== count($assigneeIds)
                                    ) {
                                        $fail('Every assignee must belong to the active workspace.');
                                    }
                                };
                            })
                            ->searchable()
                            ->preload()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
