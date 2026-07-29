<?php

namespace Tests\Feature;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskTrashTest extends TestCase
{
    use RefreshDatabase;

    public function test_task_can_be_moved_to_trash_and_restored(): void
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
            'title' => 'Trash me',
            'priority' => TaskPriority::Medium,
            'status' => TaskStatus::Pending,
        ]);

        $task->delete();

        $this->assertSoftDeleted($task);
        $this->assertNotNull($task->fresh()->deleted_at);

        $task->restore();

        $this->assertNotSoftDeleted($task);
        $this->assertNull($task->fresh()->deleted_at);
    }
}
