<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessCategory extends Model
{
    protected $fillable = ['name', 'slug', 'icon', 'map_layer', 'parent_id'];

    public function parent()
    {
        return $this->belongsTo(BusinessCategory::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(BusinessCategory::class, 'parent_id');
    }

    public function businesses()
    {
        return $this->hasMany(Business::class);
    }
}
