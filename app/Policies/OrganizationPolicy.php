<?php

namespace App\Policies;

use App\Models\Organization;
use App\Models\User;

class OrganizationPolicy
{
    public function viewAny(?User $user): bool
    {
        return true; // listado público
    }

    public function view(?User $user, Organization $organization): bool
    {
        if ($organization->status === 'approved') {
            return true;
        }

        return $user && ($user->id === $organization->user_id || $user->hasRole('admin'));
    }

    public function update(User $user, Organization $organization): bool
    {
        return $user->id === $organization->user_id || $user->hasRole('admin');
    }

    public function delete(User $user, Organization $organization): bool
    {
        return $user->hasRole('admin');
    }

    public function approve(User $user, Organization $organization): bool
    {
        return $user->hasRole('admin');
    }
}
