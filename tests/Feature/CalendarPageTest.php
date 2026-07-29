<?php

namespace Tests\Feature;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Filament\Pages\Calendar;
use App\Filament\Resources\Tasks\TaskResource;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Models\Workspace;
use Carbon\CarbonImmutable;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CalendarPageTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();

        parent::tearDown();
    }

    public function test_calendar_navigates_between_months(): void
    {
        CarbonImmutable::setTestNow('2026-07-15 00:00:00 UTC');

        [$user, $workspace] = $this->createUserAndWorkspace();
        $this->setFilamentTenant($user, $workspace);

        Livewire::test(Calendar::class)
            ->assertSet('year', 2026)
            ->assertSet('month', 7)
            ->call('previousMonth')
            ->assertSet('year', 2026)
            ->assertSet('month', 6)
            ->call('nextMonth')
            ->call('nextMonth')
            ->assertSet('year', 2026)
            ->assertSet('month', 8)
            ->call('goToToday')
            ->assertSet('year', 2026)
            ->assertSet('month', 7);
    }

    public function test_calendar_only_shows_unarchived_tasks_from_the_current_workspace(): void
    {
        CarbonImmutable::setTestNow('2026-07-15 00:00:00 UTC');

        [$user, $workspace] = $this->createUserAndWorkspace();
        [$otherUser, $otherWorkspace] = $this->createUserAndWorkspace('Other Workspace');
        $project = Project::create([
            'workspace_id' => $workspace->getKey(),
            'created_by' => $user->getKey(),
            'name' => 'Launch',
            'slug' => 'launch',
            'visibility' => 'workspace',
        ]);

        $visibleTask = $this->createTask($workspace, $user, [
            'project_id' => $project->getKey(),
            'title' => 'Visible calendar task',
            'priority' => TaskPriority::High,
            'due_at' => '2026-07-20 02:30:00',
        ]);
        $this->createTask($workspace, $user, [
            'title' => 'Archived calendar task',
            'archived_at' => now(),
            'due_at' => '2026-07-20 03:30:00',
        ]);
        $trashedTask = $this->createTask($workspace, $user, [
            'title' => 'Trashed calendar task',
            'due_at' => '2026-07-20 04:30:00',
        ]);
        $trashedTask->delete();
        $this->createTask($otherWorkspace, $otherUser, [
            'title' => 'Other workspace calendar task',
            'due_at' => '2026-07-20 05:30:00',
        ]);

        $this->setFilamentTenant($user, $workspace);
        $editUrl = TaskResource::getUrl('edit', ['record' => $visibleTask], tenant: $workspace);

        Livewire::test(Calendar::class)
            ->assertSee('July 2026')
            ->assertSee('Visible calendar task')
            ->assertSee('Launch')
            ->assertSee($editUrl, escape: false)
            ->assertDontSee('Archived calendar task')
            ->assertDontSee('Trashed calendar task')
            ->assertDontSee('Other workspace calendar task');
    }

    /**
     * @return array{User, Workspace}
     */
    private function createUserAndWorkspace(string $workspaceName = 'Test Workspace'): array
    {
        $user = User::factory()->create();
        $workspace = Workspace::create([
            'owner_id' => $user->getKey(),
            'name' => $workspaceName,
            'slug' => str($workspaceName)->slug(),
        ]);

        $workspace->members()->attach($user, [
            'role' => 'owner',
            'joined_at' => now(),
        ]);

        return [$user, $workspace];
    }

    private function setFilamentTenant(User $user, Workspace $workspace): void
    {
        $this->actingAs($user);
        Filament::setCurrentPanel(Filament::getPanel('app'));
        Filament::setTenant($workspace);
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
