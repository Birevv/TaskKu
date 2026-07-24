<?php

namespace App\Filament\Resources\Tasks\Pages;

use App\Filament\Resources\Tasks\TaskResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use App\Enums\TaskStatus;
use Override;

class CreateTask extends CreateRecord
{
    protected static string $resource = TaskResource::class;

    protected static bool $canCreateAnother = false;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth::id();
        $data['status'] = TaskStatus::Pending->value;

        return $data;
    }
        protected function getRedirectUrl(): string
        {
            return $this->getResource()::getUrl('index');
        }
}
