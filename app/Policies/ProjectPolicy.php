<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Project $project): bool
    {
        if ($project->status === 'published') {
            return true;
        }

        return $user && ($this->owns($user, $project) || $user->hasRole('admin'));
    }

    public function create(User $user): bool
    {
        return $user->organizations()->where('status', 'approved')->exists()
            || $user->hasRole('admin');
    }

    public function update(User $user, Project $project): bool
    {
        return $this->owns($user, $project) || $user->hasRole('admin');
    }

    public function delete(User $user, Project $project): bool
    {
        return $this->owns($user, $project) || $user->hasRole('admin');
    }

    private function owns(User $user, Project $project): bool
    {
        return $project->organization->user_id === $user->id;
    }
}
