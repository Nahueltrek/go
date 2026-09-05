<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProjectResource;
use App\Models\Project;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    public function show(string $slug): Response
    {
        $project = Project::published()
            ->withCoordinates()
            ->with(['organization', 'destination', 'images'])
            ->where('slug', $slug)
            ->firstOrFail();

        return Inertia::render('Public/Proyecto', [
            'project' => (new ProjectResource($project))->resolve(),
        ]);
    }
}
