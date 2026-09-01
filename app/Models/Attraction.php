<?php

namespace App\Models;

use App\Models\Concerns\HasGeoLocation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attraction extends Model
{
    use HasGeoLocation, SoftDeletes;

    protected $fillable = [
        'destination_id', 'commune_id', 'name', 'slug', 'description',
        'cover_image', 'category',
        'source', 'source_url', 'source_type', 'source_record_id',
        'imported_at', 'last_synced_at',
    ];

    protected $hidden = ['location'];

    protected function casts(): array
    {
        return [
            'imported_at' => 'datetime',
            'last_synced_at' => 'datetime',
        ];
    }

    public function destination()
    {
        return $this->belongsTo(Destination::class);
    }

    public function commune()
    {
        return $this->belongsTo(Commune::class);
    }

    public function media()
    {
        return $this->morphMany(Media::class, 'mediable');
    }
}
