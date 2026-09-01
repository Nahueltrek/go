<?php

namespace App\Models;

use App\Models\Concerns\HasGeoLocation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Ruta turística (trekking, paseo, etc.) — no confundir con las rutas HTTP
 * de Laravel. En archivos que también usan el facade de routing, importar
 * este modelo con alias: `use App\Models\Route as TrailRoute;`.
 */
class Route extends Model
{
    use HasGeoLocation, SoftDeletes;

    protected $table = 'routes';

    protected $fillable = [
        'destination_id', 'name', 'slug', 'description', 'cover_image',
        'distance_km', 'duration_minutes', 'difficulty',
    ];

    protected $hidden = ['path'];

    public function destination()
    {
        return $this->belongsTo(Destination::class);
    }

    public function points()
    {
        return $this->hasMany(RoutePoint::class)->orderBy('position');
    }
}
