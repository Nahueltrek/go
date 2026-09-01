<?php

namespace App\Models;

use App\Models\Concerns\HasGeoLocation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Activity extends Model
{
    use HasGeoLocation, SoftDeletes;

    protected $fillable = [
        'destination_id', 'business_id', 'name', 'slug', 'description',
        'difficulty', 'duration_minutes',
    ];

    protected $hidden = ['location'];

    public function destination()
    {
        return $this->belongsTo(Destination::class);
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }
}
