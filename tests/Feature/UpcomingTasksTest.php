<?php

namespace Tests\Feature;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Filament\Pages\UpcomingTasks;
use App\Models\Task;
use App\Models\User;
use App\Models\Workspace;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class UpcomingTasksTest extends TestCase
{
    use RefreshDatabase;

    public function test_upcoming_page_only_shows_active_future_tasks_from_the_current_workspace(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $workspace = $this->createWorkspace($user, 'Current Workspace');
        $otherWorkspace = $this->createWorkspace($otherUser, 'Other Workspace');

        $upcomingTask = $this->createTask($workspace, $user, [
            'title' => 'Visible upcoming task',
            'due_at' => now()->addDay(),
        ]);
        $pastTask = $this->createTask($workspace, $user, [
            'title' => 'Past task',
            'due_at' => now()->subMinute(),
        ]);
        $completedTask = $this->createTask($workspace, $user, [
            'title' => 'Completed task',
            'status' => TaskStatus::Completed,
            'due_at' => now()->addDay(),
        ]);
        $cancelledTask = $this->createTask($workspace, $user, [
            'title' => 'Cancelled task',
            'status' => TaskStatus::Cancelled,
            'due_at' => now()->addDay(),
        ]);
        $archivedTask = $this->createTask($workspace, $user, [
            'title' => 'Archived task',
            'archived_at' => now(),
            'due_at' => now()->addDay(),
        ]);
        $trashedTask = $this->createTask($workspace, $user, [
            'title' => 'Trashed task',
            'due_at' => now()->addDay(),
        ]);
        $trashedTask->delete();
        $otherWorkspaceTask = $this->createTask($otherWorkspace, $otherUser, [
            'title' => 'Other workspace task',
            'due_at' => now()->addDay(),
        ]);

        $this->actingAs($user);
        Filament::setCurrentPanel(Filament::getPanel('app'));
        Filament::setTenant($workspace);

        Livewire::test(UpcomingTasks::class)
            ->assertCanSeeTableRecords([$upcomingTask])
            ->assertCanNotSeeTableRecords([
                $pastTask,
                $completedTask,
                $cancelledTask,
                $archivedTask,
                $trashedTask,
                $otherWorkspaceTask,
            ]);
    }

    private function createWorkspace(User $owner, string $name): Workspace
    {
        $workspace = Workspace::create([
            'owner_id' => $owner->getKey(),
            'name' => $name,
            'slug' => str($name)->slug(),
        ]);

        $workspace->members()->attach($owner, [
            'role' => 'owner',
            'joined_at' => now(),
        ]);

        return $workspace;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createTask(Workspace $workspace, User $creator, array $attributes): Task
    {
        return Task::create([
            'workspace_id' => $workspace->getKey(),
            'created_by' => $creator->getKey(),
            'title' => 'Task',
            'priority' => TaskPriority::Medium,
            'status' => TaskStatus::Pending,
            ...$attributes,
        ]);
    }
}
