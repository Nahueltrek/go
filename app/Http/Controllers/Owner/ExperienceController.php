<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\BusinessCategory;
use App\Models\Destination;
use App\Models\Experience;
use App\Models\Organization;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

/**
 * CRUD del Owner sobre las Experiences de SU propia Organization (Sprint 2).
 * El Owner nunca puede fijar 'status' directamente — nace en 'draft' y solo
 * puede pasar a 'pending_review' vía submitForReview(). Publicar/rechazar
 * es exclusivo de Admin\ExperienceController::approve()/reject().
 */
class ExperienceController extends Controller
{
    public function index(Request $request): Response
    {
        $organization = $this->organizationFor($request);

        $experiences = $organization->experiences()
            ->with('activityType')
            ->latest()
            ->get()
            ->map(fn (Experience $e) => [
                'id' => $e->id,
                'name' => $e->name,
                'status' => $e->status,
                'activity_type' => $e->activityType?->name,
            ]);

        return Inertia::render('Owner/Experiencias/Index', [
            'experiences' => $experiences,
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Experience::class);

        return Inertia::render('Owner/Experiencias/Form', [
            'experience' => null,
            'destinations' => Destination::orderBy('name')->get(['id', 'name']),
            'activityTypes' => BusinessCategory::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Experience::class);

        $organization = $this->organizationFor($request);
        $validated = $this->validated($request);

        $experience = new Experience($validated);
        $experience->organization_id = $organization->id;
        $experience->status = 'draft'; // nunca se confía en input del cliente para esto
        $experience->slug = $this->uniqueSlug($validated['name'], $organization->id);
        $experience->save();

        return redirect()->route('owner.experiencias.index')->with('success', "{$experience->name} creada como borrador.");
    }

    public function edit(Request $request, Experience $experience): Response
    {
        // 'view', no 'update' — el Owner puede VER su experiencia en
        // cualquier estado (incluido pending_review), pero solo puede
        // GUARDAR cambios cuando ExperiencePolicy::update() lo permite
        // (ver ese método: bloqueado mientras está en revisión).
        $this->authorize('view', $experience);

        $experience = Experience::withCoordinates()->findOrFail($experience->id);

        return Inertia::render('Owner/Experiencias/Form', [
            'experience' => [
                'id' => $experience->id,
                'destination_id' => $experience->destination_id,
                'activity_type_id' => $experience->activity_type_id,
                'name' => $experience->name,
                'description' => $experience->description,
                'difficulty' => $experience->difficulty,
                'duration_minutes' => $experience->duration_minutes,
                'capacity' => $experience->capacity,
                'price' => $experience->price,
                'cover_image' => $experience->cover_image,
                'status' => $experience->status,
            ],
            'destinations' => Destination::orderBy('name')->get(['id', 'name']),
            'activityTypes' => BusinessCategory::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, Experience $experience): RedirectResponse
    {
        $this->authorize('update', $experience);

        $validated = $this->validated($request);

        // 'status' deliberadamente no está en $validated (ver validated())
        // — el Owner edita contenido, nunca cambia su propio estado de
        // publicación acá, ni siquiera si ya fue rechazado.
        $experience->fill($validated);

        if ($validated['name'] !== $experience->getOriginal('name')) {
            $experience->slug = $this->uniqueSlug($validated['name'], $experience->organization_id, $experience->id);
        }

        $experience->save();

        return redirect()->route('owner.experiencias.index')->with('success', "{$experience->name} actualizada.");
    }

    public function submitForReview(Experience $experience): RedirectResponse
    {
        $this->authorize('submitForReview', $experience);

        if (! in_array($experience->status, ['draft', 'rejected'], true)) {
            return back()->with('error', 'Esta experiencia ya está en revisión o publicada.');
        }

        $missing = $this->missingFieldsForReview($experience);
        if ($missing) {
            return back()->with('error', 'Antes de enviar a revisión completá: '.implode(', ', $missing).'.');
        }

        $experience->update(['status' => 'pending_review']);

        return back()->with('success', "{$experience->name} enviada a revisión.");
    }

    /**
     * Mínimos para que una Experience sea presentable públicamente — no
     * exige nada que no se pueda cargar hoy desde el formulario Owner
     * (sin sistema de subida de imágenes todavía, no se exige cover_image).
     */
    private function missingFieldsForReview(Experience $experience): array
    {
        $missing = [];

        if (! trim((string) $experience->description)) {
            $missing[] = 'descripción';
        }

        if (! $experience->activity_type_id) {
            $missing[] = 'tipo de actividad';
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
            'activity_type_id' => ['required', 'exists:business_categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'difficulty' => ['nullable', 'in:facil,medio,dificil,experto'],
            'duration_minutes' => ['nullable', 'integer', 'min:0'],
            'capacity' => ['nullable', 'integer', 'min:0'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'cover_image' => ['nullable', 'url', 'max:255'],
        ]);
    }

    protected function uniqueSlug(string $name, int $organizationId, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 2;

        while (
            Experience::where('organization_id', $organizationId)
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
