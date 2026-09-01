<?php

namespace App\Policies;

use App\Models\Experience;
use App\Models\User;

class ExperiencePolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Experience $experience): bool
    {
        if ($experience->status === 'published') {
            return true;
        }

        return $user && ($this->owns($user, $experience) || $user->hasRole('admin'));
    }

    public function create(User $user): bool
    {
        // Cualquier usuario con una organización aprobada puede crear experiencias
        return $user->organizations()->where('status', 'approved')->exists()
            || $user->hasRole('admin');
    }

    public function update(User $user, Experience $experience): bool
    {
        return $this->owns($user, $experience) || $user->hasRole('admin');
    }

    public function delete(User $user, Experience $experience): bool
    {
        return $this->owns($user, $experience) || $user->hasRole('admin');
    }

    private function owns(User $user, Experience $experience): bool
    {
        return $experience->organization->user_id === $user->id;
    }
}
