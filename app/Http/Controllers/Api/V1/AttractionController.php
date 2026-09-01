<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\AttractionResource;
use App\Models\Attraction;
use App\Models\Destination;
use Illuminate\Http\Request;

class AttractionController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'destination' => 'nullable|string|exists:destinations,slug',
            'per_page' => 'nullable|integer|min:1|max:50',
        ]);

        $query = Attraction::query()->with('commune')->withCoordinates();

        if (! empty($validated['destination'])) {
            $destination = Destination::where('slug', $validated['destination'])->firstOrFail();
            $query->where('destination_id', $destination->id);
        }

        return AttractionResource::collection($query->paginate($validated['per_page'] ?? 20));
    }

    public function show(string $slug)
    {
        $attraction = Attraction::query()
            ->where('slug', $slug)
            ->with('commune')
            ->withCoordinates()
            ->firstOrFail();

        return new AttractionResource($attraction);
    }

    public function store(Request $request)
    {
        $this->authorizeManage($request);

        $validated = $request->validate([
            'destination_id' => 'required|integer|exists:destinations,id',
            'commune_id' => 'nullable|integer|exists:communes,id',
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:attractions,slug',
            'description' => 'nullable|string',
            'cover_image' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:255',
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
        ]);

        $lat = $validated['lat'];
        $lng = $validated['lng'];
        unset($validated['lat'], $validated['lng']);

        $attraction = new Attraction($validated);
        $attraction->source = 'admin';
        $attraction->imported_at = now();
        // Igual que en Business: NOT NULL sin default, se asigna antes del
        // único save() (ver App\Models\Concerns\HasGeoLocation).
        $attraction->location = Attraction::pointExpression($lat, $lng);
        $attraction->save();

        return new AttractionResource($attraction->fresh('commune'));
    }

    protected function authorizeManage(Request $request): void
    {
        // Attraction todavía no tiene Policy propia — se reutiliza el mismo
        // criterio simple de BusinessPolicy (admin/super_admin) hasta que se
        // justifique una AttractionPolicy dedicada.
        abort_unless(
            $request->user() && ($request->user()->hasRole('admin') || $request->user()->hasRole('super_admin')),
            403
        );
    }
}
