<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrganizationMetricEvent extends Model
{
    // Solo created_at (con default CURRENT_TIMESTAMP en la migración) — no
    // hay updated_at porque un evento nunca se modifica después de crearse.
    public $timestamps = false;

    protected $fillable = ['organization_id', 'type'];

    public const TYPES = ['visit', 'whatsapp_click', 'phone_click', 'website_click', 'instagram_click'];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}
