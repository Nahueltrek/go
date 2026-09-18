<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Prospect extends Model
{
    protected $fillable = [
        'business_name', 'contact_name', 'territory', 'category',
        'instagram', 'website', 'whatsapp', 'observed_problem', 'notes',
        'source', 'status', 'last_contacted_at',
    ];

    protected function casts(): array
    {
        return [
            'last_contacted_at' => 'datetime',
        ];
    }

    // Pipeline comercial (GO_CHILE_MODELO_COMERCIAL.md §16).
    public const STATUSES = [
        'prospecto', 'contactado', 'respondio', 'demo', 'fundador',
        'activo', 'conversion_pro', 'no_responde', 'no_interesado',
        'volver_a_contactar', 'requiere_informacion', 'cerrado',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Convierte el prospecto en Organization cuando pasa a "fundador"
     * (mismo patrón que CollaborationRequest::approve()). No borra el
     * Prospect: queda como historial del pipeline, ahora enlazado.
     * plan=pro porque el programa de fundadores da GO Pro sin costo por
     * 90 días (§12) — la fecha de vencimiento del beneficio se gestiona
     * a mano por ahora, no hay todavía un job de expiración automática.
     */
    public function convertToOrganization(): Organization
    {
        $organization = Organization::create([
            'type' => 'emprendimiento',
            'name' => $this->business_name,
            'slug' => Str::slug($this->business_name),
            'instagram' => $this->instagram,
            'website' => $this->website,
            'whatsapp' => $this->whatsapp,
            'status' => 'approved',
            'plan' => 'pro',
        ]);

        $this->update(['organization_id' => $organization->id]);

        return $organization;
    }
}
