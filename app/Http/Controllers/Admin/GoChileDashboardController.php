<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\CollaborationRequest;
use App\Models\Event;
use App\Models\Experience;
use App\Models\Organization;
use App\Models\Project;
use Inertia\Inertia;
use Inertia\Response;

class GoChileDashboardController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'organizaciones_pendientes' => Organization::where('status', 'pending')->count(),
                'organizaciones_aprobadas' => Organization::where('status', 'approved')->count(),
                'colaboraciones_pendientes' => CollaborationRequest::where('status', 'pending')->count(),
                'experiencias_publicadas' => Experience::where('status', 'published')->count(),
                'proyectos_publicados' => Project::where('status', 'published')->count(),
                'eventos_proximos' => Event::published()->upcoming()->count(),
                'articulos_publicados' => BlogPost::published()->count(),
            ],
        ]);
    }
}
