<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Attraction;
use App\Models\Business;
use App\Models\BusinessCategory;
use App\Models\Destination;
use App\Models\Route as TrailRoute;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function __invoke()
    {
        $destination = Destination::active()->where('slug', 'cajon-del-maipo')->firstOrFail();

        $businesses = Business::query()
            ->active()
            ->inDestination($destination->id)
            ->with('category')
            ->withCoordinates()
            ->get();

        $attractions = Attraction::query()
            ->where('destination_id', $destination->id)
            ->withCoordinates()
            ->get();

        $routes = TrailRoute::query()
            ->where('destination_id', $destination->id)
            ->get();

        $categories = BusinessCategory::query()
            ->whereIn('slug', $destination->active_layers ?? [])
            ->orWhereIn('map_layer', $destination->active_layers ?? [])
            ->get()
            ->unique('id')
            ->values();

        return Inertia::render('Public/Home', [
            'destination' => [
                'name' => $destination->name,
                'slug' => $destination->slug,
                'description' => $destination->description,
            ],
            'categories' => $categories->map(fn ($c) => [
                'name' => $c->name,
                'slug' => $c->slug,
                'map_layer' => $c->map_layer,
            ]),
            'businesses' => $businesses->map(fn ($b) => [
                'id' => $b->id,
                'name' => $b->name,
                'slug' => $b->slug,
                'description' => $b->description,
                'address' => $b->address,
                'category' => $b->category?->name,
                'category_slug' => $b->category?->slug,
                'latitude' => $b->latitude ? (float) $b->latitude : null,
                'longitude' => $b->longitude ? (float) $b->longitude : null,
            ]),
            'attractions' => $attractions->map(fn ($a) => [
                'id' => $a->id,
                'name' => $a->name,
                'slug' => $a->slug,
                'description' => $a->description,
                'category' => $a->category,
                'latitude' => $a->latitude ? (float) $a->latitude : null,
                'longitude' => $a->longitude ? (float) $a->longitude : null,
            ]),
            'routes' => $routes->map(fn ($r) => [
                'id' => $r->id,
                'name' => $r->name,
                'slug' => $r->slug,
                'description' => $r->description,
                'distance_km' => $r->distance_km,
                'duration_minutes' => $r->duration_minutes,
                'difficulty' => $r->difficulty,
            ]),
        ]);
    }
}
