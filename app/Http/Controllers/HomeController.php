<?php

namespace App\Http\Controllers;

use App\Http\Resources\BlogPostResource;
use App\Http\Resources\EventResource;
use App\Http\Resources\ExperienceResource;
use App\Http\Resources\OrganizationResource;
use App\Http\Resources\ProjectResource;
use App\Models\BlogPost;
use App\Models\BusinessCategory;
use App\Models\Event;
use App\Models\Experience;
use App\Models\Organization;
use App\Models\Project;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    /**
     * Portada nueva de go.0km.app. Reemplaza el home institucional actual
     * (el del proyecto go-chile-laravel-v4, orientado a talleres) por una
     * experiencia de exploración, según el punto 2 del plan GO Chile 2.0.
     */
    public function index(): Response
    {
        return Inertia::render('Public/Home', [
            'activities' => BusinessCategory::orderBy('name')->get(['id', 'name', 'slug', 'icon']),
            'featuredExperiences' => ExperienceResource::collection(
                Experience::published()->where('is_featured', true)
                    ->with(['organization', 'activityType', 'images'])
                    ->limit(6)->get()
            ),
            'upcomingEvents' => EventResource::collection(
                Event::published()->upcoming()
                    ->with(['organization'])
                    ->limit(4)->get()
            ),
            'latestPosts' => BlogPostResource::collection(
                BlogPost::published()->orderByDesc('published_at')->limit(3)->get()
            ),
            'networkOrganizations' => OrganizationResource::collection(
                Organization::approved()->with('commune')
                    ->inRandomOrder()->limit(8)->get()
            ),
            'featuredProjects' => ProjectResource::collection(
                Project::published()
                    ->with(['organization', 'images'])
                    ->limit(6)->get()
            ),
        ]);
    }
}
