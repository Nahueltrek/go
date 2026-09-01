<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\BusinessResource;
use App\Models\Business;
use App\Models\Destination;
use Illuminate\Http\Request;

class MapController extends Controller
{
    /**
     * GET /api/v1/map/nearby?lat=&lng=&radius=&destino=&categoria=
     * Ver docs/GEO_MARIADB.md — usa ST_Distance_Sphere, no ST_DWithin
     * (MariaDB no lo tiene).
     */
    public function nearby(Request $request)
    {
        $validated = $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
            'radius' => 'nullable|numeric|min:1|max:50000', // metros
            'destino' => 'nullable|string|exists:destinations,slug',
            'categoria' => 'nullable|string|exists:business_categories,slug',
        ]);

        $radius = $validated['radius'] ?? 5000;

        $query = Business::query()
            ->active()
            ->with(['category', 'commune'])
            ->nearby((float) $validated['lat'], (float) $validated['lng'], (float) $radius);

        if (! empty($validated['destino'])) {
            $destination = Destination::where('slug', $validated['destino'])->firstOrFail();
            $query->inDestination($destination->id);
        }

        if (! empty($validated['categoria'])) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $validated['categoria']));
        }

        // scopeNearby ya agrega distance_m; sumamos withCoordinates aparte
        // para tener latitude/longitude también (ver HasGeoLocation).
        $query->withCoordinates();

        $businesses = $query->limit(100)->get();

        return BusinessResource::collection($businesses);
    }
}
