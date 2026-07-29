<?php

namespace Database\Seeders;

use App\Enums\ProjectVisibility;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Enums\WorkspaceRole;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()->updateOrCreate([
            'email' => 'admin@gmail.com',
        ], [
            'name' => 'Admin',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
        ]);

        $user->settings()->updateOrCreate([], [
            'timezone' => 'Asia/Jakarta',
            'theme' => 'dark',
            'notify_task_assigned' => true,
            'notify_task_due' => true,
        ]);

        $workspace = Workspace::query()->updateOrCreate([
            'slug' => 'admin-workspace',
        ], [
            'owner_id' => $user->id,
            'name' => 'Admin Workspace',
        ]);

        $workspace->members()->syncWithoutDetaching([
            $user->id => [
                'role' => WorkspaceRole::Owner->value,
                'joined_at' => now(),
            ],
        ]);

        $project = Project::withTrashed()->updateOrCreate([
            'workspace_id' => $workspace->id,
            'slug' => 'work',
        ], [
            'created_by' => $user->id,
            'name' => 'Work',
            'color' => '#4f46e5',
            'visibility' => ProjectVisibility::Workspace,
            'deleted_at' => null,
        ]);

        $task = Task::withTrashed()->updateOrCreate([
            'workspace_id' => $workspace->id,
            'project_id' => $project->id,
            'title' => 'Admin Project Q1',
        ], [
            'created_by' => $user->id,
            'description' => 'Review and leave action feedback',
            'priority' => TaskPriority::High,
            'status' => TaskStatus::Pending,
            'due_at' => now('Asia/Jakarta')
                ->addDay()
                ->setTime(10, 0)
                ->utc(),
            'deleted_at' => null,
        ]);

        $task->assignees()->syncWithoutDetaching([$user->id]);
    }
}
