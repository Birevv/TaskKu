<?php

namespace Tests\Feature;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Filament\Dashboard;
use App\Models\Task;
use App\Models\User;
use App\Models\Workspace;
use Carbon\CarbonImmutable;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardPageTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();

        parent::tearDown();
    }

    public function test_dashboard_shows_current_workspace_tasks_and_can_complete_one(): void
    {
        CarbonImmutable::setTestNow('2026-07-29 09:00:00 UTC');

        [$user, $workspace] = $this->createUserAndWorkspace('Product Team');
        [$otherUser, $otherWorkspace] = $this->createUserAndWorkspace('Other Team');

        $visibleTask = $this->createTask($workspace, $user, [
            'title' => 'Review launch copy',
            'due_at' => '2026-07-29 12:00:00',
        ]);
        $this->createTask($otherWorkspace, $otherUser, [
            'title' => 'Private task from another workspace',
            'due_at' => '2026-07-29 12:00:00',
        ]);

        $this->actingAs($user);
        Filament::setCurrentPanel(Filament::getPanel('app'));
        Filament::setTenant($workspace);

        Livewire::test(Dashboard::class)
            ->assertSee('Good morning, Maya.')
            ->assertSee('Review launch copy')
            ->assertDontSee('Private task from another workspace')
            ->call('completeTask', $visibleTask->getKey());

        $this->assertDatabaseHas('tasks', [
            'id' => $visibleTask->getKey(),
            'status' => TaskStatus::Completed->value,
        ]);
        $this->assertNotNull($visibleTask->fresh()->completed_at);
    }

    /**
     * @return array{User, Workspace}
     */
    private function createUserAndWorkspace(string $workspaceName): array
    {
        $user = User::factory()->create([
            'name' => $workspaceName === 'Product Team' ? 'Maya Hart' : 'Other User',
        ]);
        $user->settings()->create(['timezone' => 'UTC']);

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
