<?php

namespace Tests\Feature;

use App\Enums\ProjectVisibility;
use App\Filament\Resources\Tasks\Pages\CreateTask;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Models\Workspace;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TaskNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_assignee_receives_database_notification_when_task_is_created(): void
    {
        $creator = User::factory()->create();
        $assignee = User::factory()->create();
        $workspace = Workspace::create([
            'owner_id' => $creator->getKey(),
            'name' => 'Test Workspace',
            'slug' => 'test-workspace',
        ]);
        $workspace->members()->attach([
            $creator->getKey() => ['role' => 'owner', 'joined_at' => now()],
            $assignee->getKey() => ['role' => 'member', 'joined_at' => now()],
        ]);

        $this->actingAs($creator);
        Filament::setCurrentPanel(Filament::getPanel('app'));
        Filament::setTenant($workspace);

        Livewire::test(CreateTask::class)
            ->fillForm([
                'title' => 'Prepare release notes',
                'description' => 'Summarize the release.',
                'project_id' => null,
                'priority' => 'high',
                'due_at' => now()->addDay(),
                'reminder_at' => null,
                'assignees' => [$assignee->getKey()],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $task = Task::query()->sole();
        $notification = $assignee->notifications()->sole();

        $this->assertTrue($task->workspace->is($workspace));
        $this->assertTrue($task->assignees->contains($assignee));
        $this->assertSame('New task assigned', $notification->data['title']);
        $this->assertSame('Prepare release notes', $notification->data['body']);
        $this->assertCount(0, $creator->notifications);
    }

    public function test_task_form_rejects_project_and_assignee_from_another_workspace(): void
    {
        $creator = User::factory()->create();
        $outsider = User::factory()->create();
        $workspace = Workspace::create([
            'owner_id' => $creator->getKey(),
            'name' => 'Current Workspace',
            'slug' => 'current-workspace',
        ]);
        $otherWorkspace = Workspace::create([
            'owner_id' => $outsider->getKey(),
            'name' => 'Other Workspace',
            'slug' => 'other-workspace',
        ]);
        $workspace->members()->attach($creator, [
            'role' => 'owner',
            'joined_at' => now(),
        ]);
        $otherWorkspace->members()->attach($outsider, [
            'role' => 'owner',
            'joined_at' => now(),
        ]);
        $otherProject = Project::create([
            'workspace_id' => $otherWorkspace->getKey(),
            'created_by' => $outsider->getKey(),
            'name' => 'Other Project',
            'slug' => 'other-project',
            'visibility' => ProjectVisibility::Workspace,
        ]);

        $this->actingAs($creator);
        Filament::setCurrentPanel(Filament::getPanel('app'));
        Filament::setTenant($workspace);

        Livewire::test(CreateTask::class)
            ->fillForm([
                'title' => 'Tampered task',
                'description' => null,
                'project_id' => $otherProject->getKey(),
                'priority' => 'medium',
                'due_at' => null,
                'reminder_at' => null,
                'assignees' => [$outsider->getKey()],
            ])
            ->call('create')
            ->assertHasFormErrors([
                'project_id',
                'assignees',
            ]);

        $this->assertDatabaseMissing('tasks', [
            'title' => 'Tampered task',
        ]);
    }
}
