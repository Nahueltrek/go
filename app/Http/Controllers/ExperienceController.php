<?php

namespace App\Http\Controllers;

use App\Http\Resources\ExperienceResource;
use App\Models\Experience;
use Inertia\Inertia;
use Inertia\Response;

class ExperienceController extends Controller
{
    public function show(string $slug): Response
    {
        $experience = Experience::published()
            ->withCoordinates()
            ->with([
                'organization' => fn ($q) => $q->withCoordinates()->with('commune'),
                'activityType',
                'destination',
                'images',
                'reviews.user',
            ])
            ->where('slug', $slug)
            ->firstOrFail();

        return Inertia::render('Public/Experiencia', [
            'experience' => (new ExperienceResource($experience))->resolve(),
            'reviews' => $experience->reviews->map(fn ($r) => [
                'rating' => $r->rating,
                'comment' => $r->comment,
                'user_name' => $r->user?->name,
            ]),
        ]);
    }
}
