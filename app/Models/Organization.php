<?php

namespace App\Models;

use App\Models\Concerns\HasGeoLocation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Organization extends Model
{
    // NOTA: location es nullable en esta tabla (a diferencia de Business/Experience
    // en rm360, que la tienen NOT NULL con spatial index). HasGeoLocation debe
    // tolerar location = null en scopeNearby (excluir esos registros del query,
    // no fallar). Verificar ese comportamiento del trait antes de usarlo acá.
    use HasGeoLocation;

    protected $fillable = [
        'user_id', 'type', 'name', 'slug', 'description', 'commune_id',
        'instagram', 'website', 'whatsapp', 'logo_url', 'cover_image', 'status',
        'verification_status', 'claim_status', 'opening_hours',
    ];

    protected function casts(): array
    {
        return [
            'opening_hours' => 'array',
        ];
    }

    public const TYPES = [
        'guia', 'operador', 'agencia', 'emprendimiento',
        'alojamiento', 'marca', 'proyecto', 'organizacion',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function commune(): BelongsTo
    {
        return $this->belongsTo(Commune::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(BusinessCategory::class, 'organization_categories');
    }

    public function experiences(): HasMany
    {
        return $this->hasMany(Experience::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function blogPosts(): HasMany
    {
        return $this->hasMany(BlogPost::class, 'related_organization_id');
    }

    public function reviews(): MorphMany
    {
        return $this->morphMany(Review::class, 'reviewable');
    }

    public function favoritedBy(): MorphMany
    {
        return $this->morphMany(Favorite::class, 'favoritable');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Abstracción única de ownership (Sprint 2) — nunca comparar
     * organization->user_id === user->id directo en Policies/Controllers.
     * El día que exista multi-owner/editor, esta es la única línea que
     * cambia (a una consulta sobre una tabla pivote), sin tocar quien la usa.
     */
    public function isOwnedBy(User $user): bool
    {
        return $this->user_id === $user->id;
    }
}
