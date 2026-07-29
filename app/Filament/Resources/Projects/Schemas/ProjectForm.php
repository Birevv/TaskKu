<?php

namespace App\Filament\Resources\Projects\Schemas;

use App\Enums\ProjectVisibility;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProjectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Project Information')
                ->schema([
                    TextInput::make('name')
                        ->label('Project Name')
                        ->required()
                        ->maxLength(100),

                    ColorPicker::make('color')
                        ->required()
                        ->default('#4f46e5'),

                    Select::make('visibility')
                        ->options(ProjectVisibility::class)
                        ->default(ProjectVisibility::Workspace)
                        ->required(),

                    Textarea::make('description')
                        ->rows(4)
                        ->maxLength(1000)
                        ->columnSpanFull(),
                ])
                ->columns(2),
        ]);
    }
}
