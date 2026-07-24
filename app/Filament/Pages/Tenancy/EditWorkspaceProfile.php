<?php

namespace App\Filament\Pages\Tenancy;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Tenancy\EditTenantProfile;
use Filament\Schemas\Schema;

class EditWorkspaceProfile extends EditTenantProfile
{
    public static Function getLabel(): string
    {
        return 'Workspace Profile';
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->required()
                ->maxLength(100),

            TextInput::make('slug')
                ->disabled()
                ->dehydrated(false),

            Textarea::make('description')
                ->rows(4)
                ->maxLength(1000),
        ]);
    }
}

