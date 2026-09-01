<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Review extends Model
{
    protected $fillable = [
        // legacy — se mantiene hasta el drop de business_id (ver FASE_1_README)
        'business_id',
        // nuevo
        'reviewable_type', 'reviewable_id',
        'user_id', 'rating', 'comment', 'status',
    ];

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Relación polimórfica nueva. Para reviews creadas antes de esta fase,
     * reviewable_type/reviewable_id fueron backfilleados desde business_id
     * en la migración 2026_08_25_000014 — no debería haber nulls, pero
     * si algún registro los tiene, se cae al fallback business().
     */
    public function reviewable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Fallback legacy — usar solo si reviewable_id vino null.
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
