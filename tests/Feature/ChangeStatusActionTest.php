<?php

namespace Tests\Feature;

use App\Actions\Task\ChangeTaskStatusAction;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChangeStatusActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_completing_task_sets_completed_at(): void
    {
        $user = User::factory()->create();

        $workspace = Workspace::forceCreate([
            'owner_id' => $user->id,
            'name' => 'Test Workspace',
            'slug' => 'test-workspace',
        ]);

        $task = Task::forceCreate([
            'workspace_id' => $workspace->id,
            'created_by' => $user->id,
            'title' => 'Test Task',
            'priority' => TaskPriority::Medium,
            'status' => TaskStatus::Pending,

        ]);

        $result = app(ChangeTaskStatusAction::class)
            ->handle($task, TaskStatus::Completed);

        $this->assertSame(TaskStatus::Completed, $result->status);
        $this->assertNotNull($result->completed_at);
    }

    public function test_reopening_task_clears_completed_at(): void
    {
        $user = User::factory()->create();

        $workspace = Workspace::forceCreate([
            'owner_id' => $user->id,
            'name' => 'Test Workspace',
            'slug' => 'test-workspace',
        ]);

        $task = Task::forceCreate([
            'workspace_id' => $workspace->id,
            'created_by' => $user->id,
            'title' => 'Completed Task',
            'priority' => TaskPriority::Medium,
            'status' => TaskStatus::Completed,
            'completed_at' => now(),
        ]);

        $result = app(ChangeTaskStatusAction::class)
            ->handle($task, TaskStatus::Pending);

        $this->assertSame(TaskStatus::Pending, $result->status);
        $this->assertNull($result->completed_at);
    }
}
