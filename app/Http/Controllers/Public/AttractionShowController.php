<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Attraction;
use Inertia\Inertia;

class AttractionShowController extends Controller
{
    public function __invoke(string $slug)
    {
        $attraction = Attraction::query()
            ->where('slug', $slug)
            ->with(['commune', 'destination'])
            ->withCoordinates()
            ->firstOrFail();

        return Inertia::render('Public/AttractionShow', [
            'attraction' => [
                'id' => $attraction->id,
                'name' => $attraction->name,
                'slug' => $attraction->slug,
                'description' => $attraction->description,
                'category' => $attraction->category,
                'cover_image' => $attraction->cover_image,
                'commune' => $attraction->commune?->name,
                'destination' => $attraction->destination?->name,
                'source_url' => $attraction->source_url,
                'latitude' => $attraction->latitude ? (float) $attraction->latitude : null,
                'longitude' => $attraction->longitude ? (float) $attraction->longitude : null,
            ],
        ]);
    }
}
