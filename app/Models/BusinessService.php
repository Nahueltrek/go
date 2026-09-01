<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessService extends Model
{
    protected $fillable = ['business_id', 'name', 'description'];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }
}
