<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Módulo SERNATUR: tablas listas, importador inactivo (ver docs/FASE_1_README.md
 * y el entregable de Fase 0 — el piloto de Cajón del Maipo usa catastro manual).
 */
class SernaturRecord extends Model
{
    protected $fillable = [
        'rut', 'legal_name', 'trade_name', 'category', 'registration_status',
        'raw_payload', 'source', 'source_url', 'source_type', 'source_record_id',
        'imported_at', 'last_synced_at', 'geocoding_confidence',
    ];

    protected function casts(): array
    {
        return [
            'raw_payload' => 'array',
            'imported_at' => 'datetime',
            'last_synced_at' => 'datetime',
        ];
    }

    public function businesses()
    {
        return $this->hasMany(Business::class);
    }
}
