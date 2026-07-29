<?php

namespace Tests\Feature;

use App\Enums\ProjectVisibility;
use App\Filament\Resources\Projects\Pages\CreateProject;
use App\Models\Project;
use App\Models\User;
use App\Models\Workspace;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use Tests\TestCase;

class ProjectPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_manage_project_from_another_workspace(): void
    {
        $owner = User::factory()->create();
        $outsider = User::factory()->create();
        $workspace = Workspace::create([
            'owner_id' => $owner->getKey(),
            'name' => 'Owner Workspace',
            'slug' => 'owner-workspace',
        ]);
        $workspace->members()->attach($owner, [
            'role' => 'owner',
            'joined_at' => now(),
        ]);
        $project = Project::create([
            'workspace_id' => $workspace->getKey(),
            'created_by' => $owner->getKey(),
            'name' => 'Private Project',
            'slug' => 'private-project',
            'visibility' => ProjectVisibility::Workspace,
        ]);

        foreach (['view', 'update', 'delete', 'restore', 'forceDelete'] as $ability) {
            $this->assertFalse(
                Gate::forUser($outsider)->allows($ability, $project),
                "The [{$ability}] ability should reject projects from another workspace.",
            );
        }
    }

    public function test_project_creation_assigns_the_active_workspace(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::create([
            'owner_id' => $user->getKey(),
            'name' => 'Active Workspace',
            'slug' => 'active-workspace',
        ]);
        $workspace->members()->attach($user, [
            'role' => 'owner',
            'joined_at' => now(),
        ]);

        $this->actingAs($user);
        Filament::setCurrentPanel(Filament::getPanel('app'));
        Filament::setTenant($workspace);

        Livewire::test(CreateProject::class)
            ->fillForm([
                'name' => 'New Project',
                'color' => '#4f46e5',
                'visibility' => ProjectVisibility::Workspace->value,
                'description' => null,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('projects', [
            'workspace_id' => $workspace->getKey(),
            'created_by' => $user->getKey(),
            'name' => 'New Project',
            'slug' => 'new-project',
        ]);
    }
}
