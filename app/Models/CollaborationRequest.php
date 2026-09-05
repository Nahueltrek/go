<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CollaborationRequest extends Model
{
    public const TYPES = [
        'guia', 'operador', 'agencia', 'emprendimiento',
        'alojamiento', 'marca', 'proyecto', 'organizacion',
    ];

    protected $fillable = [
        'user_id', 'type', 'name', 'region_id', 'commune_id', 'instagram', 'website',
        'whatsapp', 'description', 'services', 'images', 'status',
    ];

    protected $casts = [
        'services' => 'array',
        'images' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function commune(): BelongsTo
    {
        return $this->belongsTo(Commune::class);
    }

    /**
     * Aprueba la solicitud y crea la Organization correspondiente.
     * No borra el registro de collaboration_requests: queda como historial.
     *
     * Si quien postuló estaba logueado (user_id no nulo), queda como dueño
     * de la Organization desde el momento en que se crea (Sprint 2). Si
     * postuló anónimo, la Organization nace sin dueño — un admin puede
     * asignarlo después a mano desde Admin\OrganizationController.
     */
    public function approve(): Organization
    {
        $organization = Organization::create([
            'user_id' => $this->user_id,
            'type' => $this->type,
            'name' => $this->name,
            'slug' => \Illuminate\Support\Str::slug($this->name),
            'commune_id' => $this->commune_id,
            'instagram' => $this->instagram,
            'website' => $this->website,
            'whatsapp' => $this->whatsapp,
            'description' => $this->description,
            'status' => 'approved',
        ]);

        $this->update(['status' => 'approved']);

        return $organization;
    }
}
