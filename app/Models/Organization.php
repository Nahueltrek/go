<?php

namespace App\Models;

use App\Models\Concerns\HasGeoLocation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
    ];

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

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }
}
