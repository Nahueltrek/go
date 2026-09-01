<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\BusinessResource;
use App\Models\Business;
use App\Models\Destination;
use Illuminate\Http\Request;

class BusinessController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'destination' => 'nullable|string|exists:destinations,slug',
            'category' => 'nullable|string|exists:business_categories,slug',
            'commune_id' => 'nullable|integer|exists:communes,id',
            'per_page' => 'nullable|integer|min:1|max:50',
        ]);

        $query = Business::query()
            ->active()
            ->with(['category', 'commune'])
            ->withCoordinates();

        if (! empty($validated['destination'])) {
            $destination = Destination::where('slug', $validated['destination'])->firstOrFail();
            $query->inDestination($destination->id);
        }

        if (! empty($validated['category'])) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $validated['category']));
        }

        if (! empty($validated['commune_id'])) {
            $query->where('commune_id', $validated['commune_id']);
        }

        $businesses = $query->paginate($validated['per_page'] ?? 20);

        return BusinessResource::collection($businesses);
    }

    public function show(string $slug)
    {
        $business = Business::query()
            ->active()
            ->where('slug', $slug)
            ->with(['category', 'commune', 'contacts', 'socials', 'services'])
            ->withCoordinates()
            ->firstOrFail();

        return new BusinessResource($business);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Business::class);

        $validated = $this->validatedData($request);
        $lat = $validated['lat'];
        $lng = $validated['lng'];
        unset($validated['lat'], $validated['lng']);

        $business = new Business($validated);
        $business->source = 'admin';
        $business->imported_at = now();
        // Se asigna ANTES del único save(): la columna es NOT NULL sin
        // default, así que no puede quedar afuera del primer INSERT (ver
        // App\Models\Concerns\HasGeoLocation).
        $business->location = Business::pointExpression($lat, $lng);
        $business->save();

        return new BusinessResource($business->fresh(['category', 'commune']));
    }

    public function update(Request $request, string $slug)
    {
        $business = Business::where('slug', $slug)->firstOrFail();

        $this->authorize('update', $business);

        $validated = $this->validatedData($request, updating: true);

        $business->update($validated);

        if (isset($validated['lat'], $validated['lng'])) {
            $this->assignLocation($business, $validated['lat'], $validated['lng']);
        }

        return new BusinessResource($business->fresh(['category', 'commune']));
    }

    protected function validatedData(Request $request, bool $updating = false): array
    {
        $rules = [
            'destination_id' => ($updating ? 'sometimes|' : 'required|') . 'integer|exists:destinations,id',
            'business_category_id' => 'nullable|integer|exists:business_categories,id',
            'commune_id' => 'nullable|integer|exists:communes,id',
            'locality_id' => 'nullable|integer|exists:localities,id',
            'name' => ($updating ? 'sometimes|' : 'required|') . 'string|max:255',
            'slug' => ($updating ? 'sometimes|' : 'required|') . 'string|max:255|unique:businesses,slug' . ($updating ? ',' . $request->route('slug') . ',slug' : ''),
            'description' => 'nullable|string',
            'address' => 'nullable|string|max:255',
            'is_active' => 'sometimes|boolean',
            'opening_hours' => 'nullable|array',
            'lat' => ($updating ? 'sometimes|' : 'required|') . 'numeric|between:-90,90',
            'lng' => ($updating ? 'sometimes|' : 'required|') . 'numeric|between:-180,180',
        ];

        return $request->validate($rules);
    }

    protected function assignLocation(Business $business, float $lat, float $lng): void
    {
        // No va por asignación masiva — ver App\Models\Concerns\HasGeoLocation.
        $business->location = Business::pointExpression($lat, $lng);
        $business->save();
    }
}
