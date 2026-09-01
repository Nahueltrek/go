<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\RouteResource;
use App\Models\Destination;
use App\Models\Route as TrailRoute;
use Illuminate\Http\Request;

class RouteController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'destination' => 'nullable|string|exists:destinations,slug',
            'per_page' => 'nullable|integer|min:1|max:50',
        ]);

        $query = TrailRoute::query();

        if (! empty($validated['destination'])) {
            $destination = Destination::where('slug', $validated['destination'])->firstOrFail();
            $query->where('destination_id', $destination->id);
        }

        return RouteResource::collection($query->paginate($validated['per_page'] ?? 20));
    }

    public function show(string $slug)
    {
        $route = TrailRoute::query()
            ->where('slug', $slug)
            ->with('points.pointable')
            ->firstOrFail();

        return new RouteResource($route);
    }

    public function store(Request $request)
    {
        abort_unless(
            $request->user() && ($request->user()->hasRole('admin') || $request->user()->hasRole('super_admin')),
            403
        );

        $validated = $request->validate([
            'destination_id' => 'required|integer|exists:destinations,id',
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:routes,slug',
            'description' => 'nullable|string',
            'cover_image' => 'nullable|string|max:255',
            'distance_km' => 'nullable|numeric|min:0',
            'duration_minutes' => 'nullable|integer|min:0',
            'difficulty' => 'nullable|string|max:255',
        ]);

        // 'path' (la traza GPS) queda sin cargar en esta carga manual inicial
        // — es nullable a propósito, se puede subir después vía GPX.
        $route = TrailRoute::create($validated);

        return new RouteResource($route);
    }
}
