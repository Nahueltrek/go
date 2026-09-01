<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\RouteResource;
use App\Models\Activity;
use App\Models\Attraction;
use App\Models\Business;
use App\Models\Route as TrailRoute;
use App\Models\RoutePoint;
use Illuminate\Http\Request;

class RoutePointController extends Controller
{
    // Debe coincidir con el morphMap registrado en AppServiceProvider.
    protected array $allowedTypes = [
        'business' => Business::class,
        'attraction' => Attraction::class,
        'activity' => Activity::class,
    ];

    public function store(Request $request, string $slug)
    {
        abort_unless(
            $request->user() && ($request->user()->hasRole('admin') || $request->user()->hasRole('super_admin')),
            403
        );

        $route = TrailRoute::where('slug', $slug)->firstOrFail();

        $validated = $request->validate([
            'position' => 'required|integer|min:1',
            'pointable_type' => 'required|string|in:business,attraction,activity',
            'pointable_id' => 'required|integer',
            'note' => 'nullable|string|max:255',
        ]);

        $modelClass = $this->allowedTypes[$validated['pointable_type']];
        $modelClass::findOrFail($validated['pointable_id']); // 404 claro si no existe

        RoutePoint::create([
            'route_id' => $route->id,
            'position' => $validated['position'],
            'pointable_type' => $validated['pointable_type'],
            'pointable_id' => $validated['pointable_id'],
            'note' => $validated['note'] ?? null,
        ]);

        return new RouteResource($route->fresh('points.pointable'));
    }
}
