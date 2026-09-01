<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ActivityResource;
use App\Models\Activity;
use App\Models\Destination;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'destination' => 'nullable|string|exists:destinations,slug',
            'per_page' => 'nullable|integer|min:1|max:50',
        ]);

        $query = Activity::query()->with('business')->withCoordinates();

        if (! empty($validated['destination'])) {
            $destination = Destination::where('slug', $validated['destination'])->firstOrFail();
            $query->where('destination_id', $destination->id);
        }

        return ActivityResource::collection($query->paginate($validated['per_page'] ?? 20));
    }

    public function show(string $slug)
    {
        $activity = Activity::query()
            ->where('slug', $slug)
            ->with('business')
            ->withCoordinates()
            ->firstOrFail();

        return new ActivityResource($activity);
    }
}
