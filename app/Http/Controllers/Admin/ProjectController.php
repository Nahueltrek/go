<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Destination;
use App\Models\Organization;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

/**
 * CRUD admin de Project — no existía antes del Sprint 2 (ver
 * docs/SPRINT_1_ARQUITECTURA_GO_CHILE.md §4: "Project — sin ningún CRUD
 * admin"). Mismo patrón que Admin\ExperienceController.
 */
class ProjectController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Project::class);

        $status = $request->query('status', 'all');

        $projects = Project::with('organization')
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(20)
            ->through(fn (Project $p) => [
                'id' => $p->id,
                'name' => $p->name,
                'status' => $p->status,
                'category' => $p->category,
                'organization' => $p->organization?->name,
            ]);

        return Inertia::render('Admin/Proyectos/Index', [
            'projects' => $projects,
            'activeStatus' => $status,
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Project::class);

        return Inertia::render('Admin/Proyectos/Form', [
            'project' => null,
            'organizations' => Organization::approved()->orderBy('name')->get(['id', 'name']),
            'destinations' => Destination::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Project::class);

        $validated = $this->validated($request);

        $project = new Project($validated);
        $project->slug = $this->uniqueSlug($validated['name'], $validated['organization_id']);
        $project->save();

        return redirect()->route('admin.proyectos.index')->with('success', "{$project->name} creado.");
    }

    public function edit(Project $project): Response
    {
        $this->authorize('update', $project);

        return Inertia::render('Admin/Proyectos/Form', [
            'project' => [
                'id' => $project->id,
                'organization_id' => $project->organization_id,
                'destination_id' => $project->destination_id,
                'name' => $project->name,
                'description' => $project->description,
                'category' => $project->category,
                'how_to_collaborate' => $project->how_to_collaborate,
                'status' => $project->status,
            ],
            'organizations' => Organization::approved()->orderBy('name')->get(['id', 'name']),
            'destinations' => Destination::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, Project $project): RedirectResponse
    {
        $this->authorize('update', $project);

        $validated = $this->validated($request);

        $project->fill($validated);

        if ($validated['name'] !== $project->getOriginal('name')) {
            $project->slug = $this->uniqueSlug($validated['name'], $validated['organization_id'], $project->id);
        }

        $project->save();

        return redirect()->route('admin.proyectos.index')->with('success', "{$project->name} actualizado.");
    }

    public function destroy(Project $project): RedirectResponse
    {
        $this->authorize('delete', $project);

        $name = $project->name;
        $project->delete();

        return redirect()->route('admin.proyectos.index')->with('success', "{$name} eliminado.");
    }

    public function approve(Project $project): RedirectResponse
    {
        $this->authorize('moderate', $project);

        $project->update(['status' => 'published']);

        return back()->with('success', "{$project->name} publicado.");
    }

    public function reject(Project $project): RedirectResponse
    {
        $this->authorize('moderate', $project);

        $project->update(['status' => 'rejected']);

        return back()->with('success', "{$project->name} rechazado.");
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'organization_id' => ['required', 'exists:organizations,id'],
            'destination_id' => ['nullable', 'exists:destinations,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'category' => ['required', 'in:conservacion,geologia,biodiversidad,comunidad'],
            'how_to_collaborate' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', 'in:draft,pending_review,published,unpublished,rejected'],
        ]);
    }

    protected function uniqueSlug(string $name, int $organizationId, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 2;

        while (
            Project::where('organization_id', $organizationId)
                ->where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}
