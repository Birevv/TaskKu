<?php

namespace App\Filament\Pages\Tenancy;

use App\Enums\WorkspaceRole;
use App\Models\User;
use App\Models\Workspace;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Tenancy\RegisterTenant;
use Filament\Schemas\Schema;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RegisterWorkspace extends RegisterTenant
{
    public static function getLabel(): string
    {
        return 'Create Workspace';
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Workspace Name')
                ->required()
                ->maxLength(100)
                ->autofocus(),
        ]);
    }

    protected function handleRegistration(array $data): Workspace
    {
        return DB::transaction(function () use ($data): Workspace {
            $user = Auth::user();

            if (! $user instanceof User) {
                throw new AuthenticationException;
            }

            $baseSlug = Str::slug($data['name']) ?: 'workspace';
            $slug = $baseSlug;
            $counter = 2;

            while (Workspace::where('slug', $slug)->exists()) {
                $slug = "{$baseSlug}-{$counter}";
                $counter++;
            }

            $workspace = new Workspace([
                'name' => $data['name'],
                'slug' => $slug,
            ]);

            $workspace->owner()->associate($user);
            $workspace->save();

            $workspace->members()->attach($user->id, [
                'role' => WorkspaceRole::Owner->value,
                'joined_at' => now(),
            ]);

            $user->settings()->firstOrCreate([], [
                'timezone' => 'Asia/Jakarta',
            ]);

            return $workspace;
        });
    }
}
