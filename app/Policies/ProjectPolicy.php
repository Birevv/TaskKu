<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Project $project): bool
    {
        return $this->belongsToUserWorkspace($user, $project);
    }

    public function create(User $user): bool
    {
        return $user->workspaces()->exists();
    }

    public function update(User $user, Project $project): bool
    {
        return $this->belongsToUserWorkspace($user, $project);
    }

    public function delete(User $user, Project $project): bool
    {
        return $this->belongsToUserWorkspace($user, $project);
    }

    public function restore(User $user, Project $project): bool
    {
        return $this->belongsToUserWorkspace($user, $project);
    }

    public function forceDelete(User $user, Project $project): bool
    {
        return $this->belongsToUserWorkspace($user, $project);
    }

    private function belongsToUserWorkspace(User $user, Project $project): bool
    {
        return $user->workspaces()
            ->whereKey($project->workspace_id)
            ->exists();
    }
}
