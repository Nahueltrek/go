<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoutePoint extends Model
{
    protected $fillable = ['route_id', 'position', 'pointable_type', 'pointable_id', 'note'];

    public function route()
    {
        return $this->belongsTo(Route::class);
    }

    public function pointable()
    {
        return $this->morphTo();
    }
}
