<?php

namespace App\Console\Commands;

use App\Models\Task;
use Filament\Notifications\Notification;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('tasks:send-due-reminders')]
#[Description('Send notifications for task reminders')]
class SendTaskDueReminders extends Command
{
    public function handle(): int
    {
        $start = now()->subMinute();
        $end = now();

        $tasks = Task::query()
            ->with(['assignees.settings', 'creator.settings'])
            ->whereNull('archived_at')
            ->where('status', '!=', 'completed')
            ->whereNotNull('reminder_at')
            ->whereBetween('reminder_at', [$start, $end])
            ->get();

        foreach ($tasks as $task) {
            $users = $task->assignees
                ->push($task->creator)
                ->filter()
                ->unique('id');

            foreach ($users as $user) {
                if (! ($user->settings?->notify_task_due ?? true)) {
                    continue;
                }

                Notification::make()
                    ->title('Task deadline reminder')
                    ->body($task->title)
                    ->warning()
                    ->sendToDatabase($user);
            }
        }

        $this->info("Processed {$tasks->count()} task reminder(s).");

        return self::SUCCESS;
    }
}
