<?php

namespace App\Models;

use App\Models\Concerns\HasGeoLocation;
use Illuminate\Database\Eloquent\Model;

class BusinessLocation extends Model
{
    use HasGeoLocation;

    protected $fillable = ['business_id', 'label', 'address'];
    // 'point' se asigna con BusinessLocation::pointExpression($lat, $lng),
    // igual que Business::location — ver App\Models\Concerns\HasGeoLocation.

    protected $hidden = ['point'];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    // Para traer lat/lng legibles acá, usar explícitamente:
    // BusinessLocation::withCoordinates('point')->find($id);
    // (el trait HasGeoLocation ya acepta el nombre de columna como parámetro).
}
