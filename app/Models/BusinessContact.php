<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessContact extends Model
{
    protected $fillable = ['business_id', 'phone', 'whatsapp', 'email', 'website'];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }
}
