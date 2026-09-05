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

    /**
     * El Owner puede editar en cualquier estado MENOS mientras está en
     * revisión (pending_review) — no debe poder cambiar el contenido por
     * debajo del admin que lo está evaluando. Admin no tiene esa
     * restricción, edita siempre.
     */
    public function update(User $user, Experience $experience): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $this->owns($user, $experience) && $experience->status !== 'pending_review';
    }

    public function delete(User $user, Experience $experience): bool
    {
        return $this->owns($user, $experience) || $user->hasRole('admin');
    }

    /**
     * Solo el Owner puede enviar SU PROPIO contenido a revisión — acción
     * distinta de update() porque cambia el status (draft/rejected →
     * pending_review), algo que el Owner no puede hacer libremente en
     * ningún otro campo (ver Owner\ExperienceController::submitForReview()).
     */
    public function submitForReview(User $user, Experience $experience): bool
    {
        return $this->owns($user, $experience);
    }

    /**
     * Publicar/rechazar es exclusivamente admin — nunca el Owner, ni
     * siquiera de su propia Experience.
     */
    public function moderate(User $user, Experience $experience): bool
    {
        return $user->hasRole('admin');
    }

    private function owns(User $user, Experience $experience): bool
    {
        return $experience->organization->isOwnedBy($user);
    }
}
