<?php

namespace App\Http\Controllers;

use App\Http\Resources\ExperienceResource;
use App\Http\Resources\OrganizationResource;
use App\Http\Resources\ProjectResource;
use App\Models\Organization;
use Inertia\Inertia;
use Inertia\Response;

class OrganizationController extends Controller
{
    public function show(string $slug): Response
    {
        $organization = Organization::approved()
            ->withCoordinates()
            ->with(['commune.province.region', 'categories'])
            ->where('slug', $slug)
            ->firstOrFail();

        return Inertia::render('Public/Operador', [
            'organization' => new OrganizationResource($organization),
            'experiences' => ExperienceResource::collection(
                $organization->experiences()->published()->with(['images', 'activityType'])->get()
            ),
            'projects' => ProjectResource::collection(
                $organization->projects()->published()->with('images')->get()
            ),
        ]);
    }
}
