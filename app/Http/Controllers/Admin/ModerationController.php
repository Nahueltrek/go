<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Experience;
use App\Models\Project;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Vista unificada de "contenido pendiente" (Sprint 2, decisión #7). No
 * registra moderador/fecha de acción — Experience/Project no tienen esas
 * columnas todavía. Documentado como mejora futura en vez de agregar
 * schema nuevo para esto ahora (así lo pidió explícitamente el brief).
 */
class ModerationController extends Controller
{
    public function index(): Response
    {
        // Sin $this->authorize() explícito a propósito — ExperiencePolicy::viewAny()
        // es pública (true para cualquiera). Esta acción queda protegida por el
        // middleware role:admin,super_admin del grupo de rutas admin.*, mismo
        // patrón ya usado en Admin\BusinessController/Admin\ReviewController.
        $experiences = Experience::pendingReview()
            ->with('organization.user')
            ->latest()
            ->get()
            ->map(fn (Experience $e) => [
                'type' => 'experience',
                'id' => $e->id,
                'name' => $e->name,
                'organization' => $e->organization?->name,
                'owner' => $e->organization?->user?->name,
                'created_at' => $e->created_at->format('d-m-Y H:i'),
                'cover_image' => $e->cover_image,
                'edit_url' => "/admin/experiencias/{$e->id}/editar",
                'approve_url' => "/admin/experiencias/{$e->id}/approve",
                'reject_url' => "/admin/experiencias/{$e->id}/reject",
            ]);

        $projects = Project::pendingReview()
            ->with('organization.user')
            ->latest()
            ->get()
            ->map(fn (Project $p) => [
                'type' => 'project',
                'id' => $p->id,
                'name' => $p->name,
                'organization' => $p->organization?->name,
                'owner' => $p->organization?->user?->name,
                'created_at' => $p->created_at->format('d-m-Y H:i'),
                'edit_url' => "/admin/proyectos/{$p->id}/editar",
                'approve_url' => "/admin/proyectos/{$p->id}/approve",
                'reject_url' => "/admin/proyectos/{$p->id}/reject",
            ]);

        return Inertia::render('Admin/Moderacion', [
            'experiences' => $experiences->values(),
            'projects' => $projects->values(),
        ]);
    }
}
