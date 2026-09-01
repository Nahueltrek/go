<?php

namespace App\Models;

use App\Models\Concerns\HasGeoLocation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Business extends Model
{
    use HasFactory, HasGeoLocation, SoftDeletes;

    protected $fillable = [
        'destination_id',
        'business_category_id',
        'commune_id',
        'locality_id',
        'owner_id',
        'name',
        'slug',
        'description',
        'address',
        'sernatur_status',
        'sernatur_record_id',
        'verification_status',
        'claim_status',
        'is_active',
        'opening_hours',
        'source',
        'source_url',
        'source_type',
        'source_record_id',
        'imported_at',
        'last_synced_at',
        // 'location' se asigna aparte con Business::pointExpression(), nunca
        // por asignación masiva (ver App\Models\Concerns\HasGeoLocation).
    ];

    protected $hidden = ['location'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'opening_hours' => 'array',
            'imported_at' => 'datetime',
            'last_synced_at' => 'datetime',
        ];
    }

    public function destination()
    {
        return $this->belongsTo(Destination::class);
    }

    public function category()
    {
        return $this->belongsTo(BusinessCategory::class, 'business_category_id');
    }

    public function commune()
    {
        return $this->belongsTo(Commune::class);
    }

    public function locality()
    {
        return $this->belongsTo(Locality::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function sernaturRecord()
    {
        return $this->belongsTo(SernaturRecord::class);
    }

    public function services()
    {
        return $this->hasMany(BusinessService::class);
    }

    public function locations()
    {
        return $this->hasMany(BusinessLocation::class);
    }

    public function contacts()
    {
        return $this->hasMany(BusinessContact::class);
    }

    public function socials()
    {
        return $this->hasMany(BusinessSocial::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function claims()
    {
        return $this->hasMany(Claim::class);
    }

    public function verifications()
    {
        return $this->hasMany(Verification::class);
    }

    public function media()
    {
        return $this->morphMany(Media::class, 'mediable');
    }

    public function favoritedBy()
    {
        return $this->morphMany(Favorite::class, 'favoritable');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInDestination($query, int $destinationId)
    {
        return $query->where('destination_id', $destinationId);
    }
}
