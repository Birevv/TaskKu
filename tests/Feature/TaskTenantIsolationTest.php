<?php

namespace Tests\Feature;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Enums\WorkspaceRole;
use App\Models\Task;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class TaskTenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_view_task_from_antoher_workspace(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $workspaceA = Workspace::create([
            'owner_id' => $userA->id,
            'name' => 'Workspace A',
            'slug' => 'workspace-a',
        ]);

        $workspaceB = Workspace::create([
            'owner_id' => $userB->id,
            'name' => 'Workspace B',
            'slug' => 'workspace-b',
        ]);

        $workspaceA->members()->attach($userA->id, [
            'role' => WorkspaceRole::Owner->value,
            'joined_at' => now(),
        ]);

        $workspaceB->members()->attach($userB->id, [
            'role' => WorkspaceRole::Owner->value,
            'joined_at' => now(),
        ]);

        $taskA = Task::create([
            'workspace_id' => $workspaceA->id,
            'created_by' => $userA->id,
            'title' => 'Task User A',
            'priority' => TaskPriority::Medium,
            'status' => TaskStatus::Pending,
        ]);

        $taskB = Task::create([
            'workspace_id' => $workspaceB->id,
            'created_by' => $userB->id,
            'title' => 'Task User B',
            'priority' => TaskPriority::High,
            'status' => TaskStatus::Pending,
        ]);

        $this->assertTrue(
            Gate::forUser($userA)->allows('view', $taskA)
        );

        $this->assertFalse(
            Gate::forUser($userA)->allows('view', $taskB)
        );
    }
}
