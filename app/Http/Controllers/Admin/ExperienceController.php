<?php

namespace App\Http\Controllers\Admin;

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

class ExperienceController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Experience::class);

        $status = $request->query('status', 'all');

        $experiences = Experience::with(['organization', 'activityType'])
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(20)
            ->through(fn (Experience $e) => [
                'id' => $e->id,
                'name' => $e->name,
                'status' => $e->status,
                'is_featured' => $e->is_featured,
                'organization' => $e->organization?->name,
                'activity_type' => $e->activityType?->name,
                'price' => $e->price,
            ]);

        return Inertia::render('Admin/Experiencias/Index', [
            'experiences' => $experiences,
            'activeStatus' => $status,
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Experience::class);

        return Inertia::render('Admin/Experiencias/Form', [
            'experience' => null,
            'organizations' => Organization::approved()->orderBy('name')->get(['id', 'name']),
            'destinations' => Destination::orderBy('name')->get(['id', 'name']),
            'activityTypes' => BusinessCategory::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Experience::class);

        $validated = $this->validated($request);

        $experience = new Experience($this->fillableData($validated));
        $experience->slug = $this->uniqueSlug($validated['name'], $validated['organization_id']);

        if (! empty($validated['latitude']) && ! empty($validated['longitude'])) {
            $experience->location = Experience::pointExpression($validated['latitude'], $validated['longitude']);
        }

        $experience->save();

        return redirect()->route('admin.experiencias.index')->with('success', "{$experience->name} creada.");
    }

    public function edit(Experience $experience): Response
    {
        $this->authorize('update', $experience);

        $experience = Experience::withCoordinates()->findOrFail($experience->id);

        return Inertia::render('Admin/Experiencias/Form', [
            'experience' => [
                'id' => $experience->id,
                'organization_id' => $experience->organization_id,
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
                'is_featured' => $experience->is_featured,
                'latitude' => $experience->latitude,
                'longitude' => $experience->longitude,
            ],
            'organizations' => Organization::approved()->orderBy('name')->get(['id', 'name']),
            'destinations' => Destination::orderBy('name')->get(['id', 'name']),
            'activityTypes' => BusinessCategory::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, Experience $experience): RedirectResponse
    {
        $this->authorize('update', $experience);

        $validated = $this->validated($request);

        $experience->fill($this->fillableData($validated));

        if ($validated['name'] !== $experience->getOriginal('name')) {
            $experience->slug = $this->uniqueSlug($validated['name'], $validated['organization_id'], $experience->id);
        }

        if (! empty($validated['latitude']) && ! empty($validated['longitude'])) {
            $experience->location = Experience::pointExpression($validated['latitude'], $validated['longitude']);
        }

        $experience->save();

        return redirect()->route('admin.experiencias.index')->with('success', "{$experience->name} actualizada.");
    }

    public function destroy(Experience $experience): RedirectResponse
    {
        $this->authorize('delete', $experience);

        $name = $experience->name;
        $experience->delete();

        return redirect()->route('admin.experiencias.index')->with('success', "{$name} eliminada.");
    }

    public function approve(Experience $experience): RedirectResponse
    {
        $this->authorize('moderate', $experience);

        $experience->update(['status' => 'published']);

        return back()->with('success', "{$experience->name} publicada.");
    }

    public function reject(Experience $experience): RedirectResponse
    {
        $this->authorize('moderate', $experience);

        $experience->update(['status' => 'rejected']);

        return back()->with('success', "{$experience->name} rechazada.");
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'organization_id' => ['required', 'exists:organizations,id'],
            'destination_id' => ['nullable', 'exists:destinations,id'],
            'activity_type_id' => ['required', 'exists:business_categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'difficulty' => ['nullable', 'in:facil,medio,dificil,experto'],
            'duration_minutes' => ['nullable', 'integer', 'min:0'],
            'capacity' => ['nullable', 'integer', 'min:0'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'cover_image' => ['nullable', 'url', 'max:255'],
            'status' => ['required', 'in:draft,pending_review,published,unpublished,rejected'],
            'is_featured' => ['boolean'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);
    }

    protected function fillableData(array $validated): array
    {
        return collect($validated)->except(['latitude', 'longitude'])->toArray();
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
