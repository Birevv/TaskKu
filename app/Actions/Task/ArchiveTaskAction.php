<?php

namespace App\Actions\Task;

use App\Models\Task;

class ArchiveTaskAction
{
    public function handle(Task $task): Task
    {
        $task->update([
            'archived_at' => now(),
        ]);

        return $task->refresh();
    }
}
