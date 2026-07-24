<?php

namespace App\Filament\Resources\Projects\Pages;

use App\Filament\Resources\Projects\ProjectResource;
use App\Models\Project;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class CreateProject extends CreateRecord
{
    protected static string $resource = ProjectResource::class;

    protected static bool $canCreateAnother = false;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = Auth::id();

        $baseSlug = Str::slug($data['name']) ?: 'project';
        $slug = $baseSlug;
        $counter = 2;

        while (
            Project::query()
                ->whereBelongsTo(Filament::getTenant())
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        $data['slug'] = $slug;

        return $data;

    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
