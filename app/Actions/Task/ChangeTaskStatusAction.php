<?php

namespace App\Actions\Task;

use App\Enums\TaskStatus;
use App\Models\Task;
use Illuminate\Support\Facades\DB;

class ChangeTaskStatusAction
{
    public function handle(Task $task, TaskStatus $status): Task
    {
        return DB::transaction(function () use ($task, $status): Task {
            $task->status = $status;

            if ($status ===  TaskStatus::Completed) {
                $task->completed_at ??= now();
            } else {
                $task->completed_at = null;
            }

            $task->save();

            return $task->refresh();
        });
    }
}
