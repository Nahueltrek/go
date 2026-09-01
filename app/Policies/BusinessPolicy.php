<?php

namespace App\Policies;

use App\Models\Business;
use App\Models\User;

class BusinessPolicy
{
    public function viewAny(?User $user): bool
    {
        return true; // el listado público es abierto
    }

    public function view(?User $user, Business $business): bool
    {
        return $business->is_active || $this->manage($user);
    }

    public function create(User $user): bool
    {
        return $user->hasRole('super_admin') || $user->hasRole('admin');
    }

    public function update(User $user, Business $business): bool
    {
        if ($this->manage($user)) {
            return true;
        }

        // El dueño solo puede editar SU ficha, y solo si ya está reclamada
        // y aprobada — nunca los campos de origen SERNATUR (eso se controla
        // en el FormRequest/Resource, no acá: la Policy autoriza la acción
        // de "editar contenido propio", no campo por campo).
        return $business->owner_id === $user->id
            && $business->claim_status === 'claimed';
    }

    public function delete(User $user, Business $business): bool
    {
        return $this->manage($user);
    }

    public function restore(User $user, Business $business): bool
    {
        return $this->manage($user);
    }

    public function forceDelete(User $user, Business $business): bool
    {
        return $user->hasRole('super_admin');
    }

    public function claim(User $user, Business $business): bool
    {
        return $business->claim_status === 'unclaimed';
    }

    public function reviewClaim(User $user, Business $business): bool
    {
        return $this->manage($user);
    }

    public function verify(User $user, Business $business): bool
    {
        return $this->manage($user);
    }

    protected function manage(?User $user): bool
    {
        return $user && ($user->hasRole('super_admin') || $user->hasRole('admin'));
    }
}
