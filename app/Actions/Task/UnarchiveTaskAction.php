<?php

namespace App\Actions\Task;

use App\Models\Task;

class UnarchiveTaskAction
{
    public function handle(Task $task): Task
    {
        $task->update([
            'archived_at' => null,
        ]);

        return $task->refresh();
    }
}
