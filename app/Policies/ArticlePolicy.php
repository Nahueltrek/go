<?php

namespace App\Policies;

use App\Models\Article;
use App\Models\User;

class ArticlePolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Article $article): bool
    {
        return $article->status === 'published' || $this->canManage($user);
    }

    public function create(User $user): bool
    {
        return $this->canManage($user);
    }

    public function update(User $user, Article $article): bool
    {
        return $this->canManage($user);
    }

    public function delete(User $user, Article $article): bool
    {
        return $user->hasRole('super_admin') || $user->hasRole('admin');
    }

    public function publish(User $user, Article $article): bool
    {
        // A diferencia de crear/editar (que un editor puede hacer), publicar
        // requiere admin — así se preserva el control editorial final.
        return $user->hasRole('super_admin') || $user->hasRole('admin');
    }

    protected function canManage(User $user): bool
    {
        return $user->hasRole('super_admin') || $user->hasRole('admin') || $user->hasRole('editor');
    }
}
