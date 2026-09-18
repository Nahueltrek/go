<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlogPost extends Model
{
    /**
     * Las 6 categorías editoriales de la Bitácora GO (Sprint Bitácora GO 1.0).
     * Coinciden 1 a 1 con el enum de la columna `category` en la migración
     * create_blog_posts_table — no cambiar claves sin migrar la columna.
     */
    public const CATEGORIES = [
        'rutas' => 'Rutas',
        'personas' => 'Personas',
        'territorio' => 'Territorio',
        'educacion' => 'Educación',
        'conservacion' => 'Conservación',
        'experiencias' => 'Experiencias',
    ];

    public const STATUSES = ['draft', 'published', 'unpublished'];

    protected $fillable = [
        'author_id', 'title', 'slug', 'excerpt', 'content', 'category',
        'cover_image_url', 'related_organization_id', 'related_destination_id',
        'related_route_id', 'related_experience_id', 'related_project_id',
        'status', 'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function relatedOrganization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'related_organization_id');
    }

    public function relatedDestination(): BelongsTo
    {
        return $this->belongsTo(Destination::class, 'related_destination_id');
    }

    public function relatedRoute(): BelongsTo
    {
        return $this->belongsTo(Route::class, 'related_route_id');
    }

    public function relatedExperience(): BelongsTo
    {
        return $this->belongsTo(Experience::class, 'related_experience_id');
    }

    public function relatedProject(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'related_project_id');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published')->whereNotNull('published_at');
    }
}
