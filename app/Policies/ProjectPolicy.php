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

    /**
     * Ver ExperiencePolicy::update() — mismo patrón: el Owner no puede
     * editar mientras está en pending_review, admin siempre puede.
     */
    public function update(User $user, Project $project): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $this->owns($user, $project) && $project->status !== 'pending_review';
    }

    public function delete(User $user, Project $project): bool
    {
        return $this->owns($user, $project) || $user->hasRole('admin');
    }

    /**
     * Ver ExperiencePolicy::submitForReview() — mismo patrón.
     */
    public function submitForReview(User $user, Project $project): bool
    {
        return $this->owns($user, $project);
    }

    /**
     * Ver ExperiencePolicy::moderate() — mismo patrón.
     */
    public function moderate(User $user, Project $project): bool
    {
        return $user->hasRole('admin');
    }

    private function owns(User $user, Project $project): bool
    {
        return $project->organization->isOwnedBy($user);
    }
}
