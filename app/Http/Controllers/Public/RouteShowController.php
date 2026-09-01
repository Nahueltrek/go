<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Attraction;
use App\Models\Business;
use App\Models\Route as TrailRoute;
use Inertia\Inertia;

class RouteShowController extends Controller
{
    // Debe coincidir con el morphMap de AppServiceProvider.
    protected array $modelsByType = [
        'business' => Business::class,
        'attraction' => Attraction::class,
        'activity' => Activity::class,
    ];

    public function __invoke(string $slug)
    {
        $route = TrailRoute::query()
            ->where('slug', $slug)
            ->with('points')
            ->firstOrFail();

        // Los puntos son polimórficos y necesitan latitude/longitude vía
        // withCoordinates() (ver App\Models\Concerns\HasGeoLocation), que no
        // se puede aplicar directo sobre un eager-load morphTo — se resuelven
        // uno por uno. El dataset de una ruta es chico (unos pocos puntos),
        // así que el costo de N consultas extra acá es aceptable.
        $points = $route->points->map(function ($point) {
            $modelClass = $this->modelsByType[$point->pointable_type] ?? null;
            $item = $modelClass
                ? $modelClass::query()->withCoordinates()->find($point->pointable_id)
                : null;

            return [
                'position' => $point->position,
                'type' => $point->pointable_type,
                'note' => $point->note,
                'item' => $item ? [
                    'name' => $item->name,
                    'slug' => $item->slug,
                    'latitude' => $item->latitude ? (float) $item->latitude : null,
                    'longitude' => $item->longitude ? (float) $item->longitude : null,
                ] : null,
            ];
        })->sortBy('position')->values();

        return Inertia::render('Public/RouteShow', [
            'route' => [
                'id' => $route->id,
                'name' => $route->name,
                'slug' => $route->slug,
                'description' => $route->description,
                'distance_km' => $route->distance_km,
                'duration_minutes' => $route->duration_minutes,
                'difficulty' => $route->difficulty,
                'points' => $points,
            ],
        ]);
    }
}
