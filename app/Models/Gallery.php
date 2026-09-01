<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gallery extends Model
{
    protected $fillable = ['galleryable_type', 'galleryable_id', 'title'];

    public function galleryable()
    {
        return $this->morphTo();
    }
}
