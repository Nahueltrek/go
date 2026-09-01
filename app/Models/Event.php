<?php

namespace App\Models;

use App\Models\Concerns\HasGeoLocation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use HasGeoLocation, SoftDeletes;

    protected $fillable = [
        'destination_id',
        'business_id',        // legacy — se conserva, un evento puede seguir ligado a un Business
        'organization_id',    // nuevo — operadores de GO Chile 2.0
        'title', 'slug', 'description',
        'category',            // nuevo
        'cover_image',
        'starts_at', 'ends_at',
        'capacity', 'price', 'difficulty', 'status', // nuevos
    ];

    protected $hidden = ['location'];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class);
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function images(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable')->orderBy('order');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('starts_at', '>=', now())->orderBy('starts_at');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }
}
