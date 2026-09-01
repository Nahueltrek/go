<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Locality extends Model
{
    protected $fillable = ['commune_id', 'name'];

    protected $hidden = ['center'];

    public function commune()
    {
        return $this->belongsTo(Commune::class);
    }

    public function businesses()
    {
        return $this->hasMany(Business::class);
    }
}
