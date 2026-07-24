<?php

namespace App\Filament\Resources\Tasks\Schemas;

use App\Enums\TaskPriority;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

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
                            )
                            ->searchable()
                            ->preload(),

                        Select::make('priority')
                            ->options(TaskPriority::class)
                            ->default(TaskPriority::Medium)
                            ->required(),

                        DateTimePicker::make('due_at')
                            ->label("Due date")
                            ->seconds(false)
                            ->native(false),

                        DateTimePicker::make('reminder_at')
                            ->label("Reminder")
                            ->seconds(false)
                            ->native(false)
                            ->beforeOrEqual('due_at'),

                        Select::make('assginees')
                            ->Label('Assignees')
                            ->relationship(
                                name: 'assignees',
                                titleAttribute: 'name'
                            )
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->columnSpanFull()
                    ])
                    ->columns(2),
            ]);
    }
}
