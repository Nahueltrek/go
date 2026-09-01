<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventResource;
use App\Models\Destination;
use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'destination' => 'nullable|string|exists:destinations,slug',
            'per_page' => 'nullable|integer|min:1|max:50',
        ]);

        $query = Event::query()->upcoming()->with('business')->withCoordinates();

        if (! empty($validated['destination'])) {
            $destination = Destination::where('slug', $validated['destination'])->firstOrFail();
            $query->where('destination_id', $destination->id);
        }

        return EventResource::collection($query->paginate($validated['per_page'] ?? 20));
    }

    public function show(string $slug)
    {
        $event = Event::query()
            ->where('slug', $slug)
            ->with('business')
            ->withCoordinates()
            ->firstOrFail();

        return new EventResource($event);
    }
}
