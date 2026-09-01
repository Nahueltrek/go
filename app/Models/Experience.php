<?php

namespace App\Models;

use App\Models\Concerns\HasGeoLocation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Experience extends Model
{
    use HasGeoLocation;

    protected $fillable = [
        'organization_id', 'destination_id', 'activity_type_id', 'name', 'slug',
        'description', 'difficulty', 'duration_minutes', 'capacity', 'price', 'status', 'is_featured',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class);
    }

    public function activityType(): BelongsTo
    {
        return $this->belongsTo(BusinessCategory::class, 'activity_type_id');
    }

    public function images(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable')->orderBy('order');
    }

    public function reviews(): MorphMany
    {
        return $this->morphMany(Review::class, 'reviewable');
    }

    public function favoritedBy(): MorphMany
    {
        return $this->morphMany(Favorite::class, 'favoritable');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }
}
