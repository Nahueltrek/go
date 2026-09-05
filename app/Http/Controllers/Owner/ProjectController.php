<?php

namespace App\Http\Controllers\Owner;

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
 * CRUD del Owner sobre los Projects de SU propia Organization (Sprint 2).
 * Mismo patrón que Owner\ExperienceController — ver ese archivo para el
 * razonamiento completo sobre por qué 'status' nunca viene del cliente.
 */
class ProjectController extends Controller
{
    public function index(Request $request): Response
    {
        $organization = $this->organizationFor($request);

        $projects = $organization->projects()
            ->latest()
            ->get()
            ->map(fn (Project $p) => [
                'id' => $p->id,
                'name' => $p->name,
                'status' => $p->status,
                'category' => $p->category,
            ]);

        return Inertia::render('Owner/Proyectos/Index', [
            'projects' => $projects,
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Project::class);

        return Inertia::render('Owner/Proyectos/Form', [
            'project' => null,
            'destinations' => Destination::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Project::class);

        $organization = $this->organizationFor($request);
        $validated = $this->validated($request);

        $project = new Project($validated);
        $project->organization_id = $organization->id;
        $project->status = 'draft';
        $project->slug = $this->uniqueSlug($validated['name'], $organization->id);
        $project->save();

        return redirect()->route('owner.proyectos.index')->with('success', "{$project->name} creado como borrador.");
    }

    public function edit(Request $request, Project $project): Response
    {
        // Ver el comentario equivalente en Owner\ExperienceController::edit().
        $this->authorize('view', $project);

        return Inertia::render('Owner/Proyectos/Form', [
            'project' => [
                'id' => $project->id,
                'destination_id' => $project->destination_id,
                'name' => $project->name,
                'description' => $project->description,
                'category' => $project->category,
                'how_to_collaborate' => $project->how_to_collaborate,
                'status' => $project->status,
            ],
            'destinations' => Destination::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, Project $project): RedirectResponse
    {
        $this->authorize('update', $project);

        $validated = $this->validated($request);

        $project->fill($validated);

        if ($validated['name'] !== $project->getOriginal('name')) {
            $project->slug = $this->uniqueSlug($validated['name'], $project->organization_id, $project->id);
        }

        $project->save();

        return redirect()->route('owner.proyectos.index')->with('success', "{$project->name} actualizado.");
    }

    public function submitForReview(Project $project): RedirectResponse
    {
        $this->authorize('submitForReview', $project);

        if (! in_array($project->status, ['draft', 'rejected'], true)) {
            return back()->with('error', 'Este proyecto ya está en revisión o publicado.');
        }

        $missing = $this->missingFieldsForReview($project);
        if ($missing) {
            return back()->with('error', 'Antes de enviar a revisión completá: '.implode(', ', $missing).'.');
        }

        $project->update(['status' => 'pending_review']);

        return back()->with('success', "{$project->name} enviado a revisión.");
    }

    /**
     * Ver el comentario equivalente en Owner\ExperienceController.
     */
    private function missingFieldsForReview(Project $project): array
    {
        $missing = [];

        if (! trim((string) $project->description)) {
            $missing[] = 'descripción';
        }

        if (! trim((string) $project->how_to_collaborate)) {
            $missing[] = 'cómo colaborar';
        }

        return $missing;
    }

    private function organizationFor(Request $request): Organization
    {
        return $request->user()->organizations()->firstOrFail();
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'destination_id' => ['nullable', 'exists:destinations,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'category' => ['required', 'in:conservacion,geologia,biodiversidad,comunidad'],
            'how_to_collaborate' => ['nullable', 'string', 'max:2000'],
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
