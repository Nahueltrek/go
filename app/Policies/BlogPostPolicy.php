<?php

namespace App\Policies;

use App\Models\BlogPost;
use App\Models\User;

class BlogPostPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, BlogPost $post): bool
    {
        if ($post->status === 'published') {
            return true;
        }

        return $user && ($user->id === $post->author_id || $user->hasRole('admin'));
    }

    public function create(User $user): bool
    {
        // Editorial: solo admin/editor publican en la Bitácora, a diferencia
        // de experiences/projects que cualquier organización aprobada puede crear
        return $user->hasRole('admin') || $user->hasRole('editor');
    }

    public function update(User $user, BlogPost $post): bool
    {
        return $user->id === $post->author_id || $user->hasRole('admin');
    }

    public function delete(User $user, BlogPost $post): bool
    {
        return $user->hasRole('admin');
    }
}
