<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Nombres cortos en las columnas *_type de las relaciones polimórficas
        // propias del proyecto (route_points.pointable_type,
        // article_relations.relatable_type, media/galleries.*able_type,
        // favorites.favoritable_type). Se usa morphMap() sin "enforce": Sanctum
        // usa su propia relación polimórfica interna (tokens()) sobre User, y
        // enforceMorphMap() rompe con cualquier modelo no listado acá,
        // incluidos los que usan paquetes de terceros.
        Relation::morphMap([
            'business' => \App\Models\Business::class,
            'attraction' => \App\Models\Attraction::class,
            'activity' => \App\Models\Activity::class,
            'route' => \App\Models\Route::class,
            'article' => \App\Models\Article::class,
            'destination' => \App\Models\Destination::class,
        ]);
    }
}
