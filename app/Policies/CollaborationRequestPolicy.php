<?php

namespace App\Policies;

use App\Models\CollaborationRequest;
use App\Models\User;

class CollaborationRequestPolicy
{
    // create() no aplica policy — el form "Quiero ser parte de GO Chile" es público,
    // no requiere estar autenticado.

    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function view(User $user, CollaborationRequest $request): bool
    {
        return $user->hasRole('admin');
    }

    public function approve(User $user, CollaborationRequest $request): bool
    {
        return $user->hasRole('admin');
    }

    public function reject(User $user, CollaborationRequest $request): bool
    {
        return $user->hasRole('admin');
    }
}
