<?php

namespace App\Filament\Resources\Tasks\Pages;

use App\Enums\TaskStatus;
use App\Filament\Resources\Tasks\TaskResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateTask extends CreateRecord
{
    protected static string $resource = TaskResource::class;

    protected static bool $canCreateAnother = false;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = Auth::id();
        $data['status'] = TaskStatus::Pending->value;

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterCreate(): void
    {
        $this->record->load('assignees');

        foreach ($this->record->assignees as $assignee) {
            if ($assignee->is(Auth::user())) {
                continue;
            }

            if (! ($assignee->settings?->notify_task_assigned ?? true)) {
                continue;
            }

            Notification::make()
                ->title('New task assigned')
                ->body($this->record->title)
                ->sendToDatabase($assignee);
        }
    }
}
