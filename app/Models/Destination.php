<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Destination extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'cover_image', 'is_active', 'active_layers'];

    protected $hidden = ['boundary'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'active_layers' => 'array',
        ];
    }

    public function businesses()
    {
        return $this->hasMany(Business::class);
    }

    public function attractions()
    {
        return $this->hasMany(Attraction::class);
    }

    public function activities()
    {
        return $this->hasMany(Activity::class);
    }

    public function routes()
    {
        return $this->hasMany(Route::class);
    }

    public function articles()
    {
        return $this->hasMany(Article::class);
    }

    public function events()
    {
        return $this->hasMany(Event::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
