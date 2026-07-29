<?php

namespace Tests\Feature;

use App\Actions\Task\ArchiveTaskAction;
use App\Actions\Task\UnarchiveTaskAction;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskArchiveTest extends TestCase
{
    use RefreshDatabase;

    public function test_task_can_be_archived_and_unarchived(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::create([
            'owner_id' => $user->getKey(),
            'name' => 'Test Workspace',
            'slug' => 'test-workspace',
        ]);
        $task = Task::create([
            'workspace_id' => $workspace->getKey(),
            'created_by' => $user->getKey(),
            'title' => 'Archive me',
            'priority' => TaskPriority::Medium,
            'status' => TaskStatus::Pending,
        ]);

        $archivedTask = app(ArchiveTaskAction::class)->handle($task);

        $this->assertNotNull($archivedTask->archived_at);
        $this->assertDatabaseHas('tasks', [
            'id' => $task->getKey(),
            'archived_at' => $archivedTask->archived_at,
        ]);

        $unarchivedTask = app(UnarchiveTaskAction::class)->handle($archivedTask);

        $this->assertNull($unarchivedTask->archived_at);
        $this->assertDatabaseHas('tasks', [
            'id' => $task->getKey(),
            'archived_at' => null,
        ]);
    }
}
