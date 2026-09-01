<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrganizationResource;
use App\Models\BusinessCategory;
use App\Models\Organization;
use App\Models\Region;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrganizationController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Organization::class);

        $status = $request->query('status', 'approved');

        $organizations = Organization::with(['commune', 'categories'])
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(20);

        return Inertia::render('Admin/Organizaciones', [
            'organizations' => OrganizationResource::collection($organizations),
            'activeStatus' => $status,
        ]);
    }

    public function approve(Organization $organization): RedirectResponse
    {
        $this->authorize('approve', $organization);

        $organization->update(['status' => 'approved']);

        return back()->with('success', "{$organization->name} aprobado.");
    }

    public function suspend(Organization $organization): RedirectResponse
    {
        $this->authorize('approve', $organization); // mismo permiso que approve — solo admin

        $organization->update(['status' => 'rejected']);

        return back()->with('success', "{$organization->name} suspendido.");
    }

    public function edit(Organization $organization): Response
    {
        $this->authorize('update', $organization);

        // withCoordinates() agrega latitude/longitude planos (ver HasGeoLocation)
        // que OrganizationResource ya sabe leer para armar 'location'.
        $organization = Organization::withCoordinates()
            ->with(['commune.province.region', 'categories'])
            ->findOrFail($organization->id);

        return Inertia::render('Admin/OrganizacionesEdit', [
            'organization' => (new OrganizationResource($organization))->resolve()
                + ['category_ids' => $organization->categories->pluck('id')],
            'regions' => Region::with('provinces.communes')->orderBy('name')->get(),
            'categories' => BusinessCategory::orderBy('name')->get(['id', 'name', 'slug']),
        ]);
    }

    public function update(Request $request, Organization $organization): RedirectResponse
    {
        $this->authorize('update', $organization);

        $validated = $request->validate([
            'commune_id' => ['nullable', 'exists:communes,id'],
            'instagram' => ['nullable', 'string', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            'whatsapp' => ['nullable', 'string', 'max:30'],
            'description' => ['nullable', 'string', 'max:2000'],
            'logo_url' => ['nullable', 'url', 'max:255'],
            'cover_image' => ['nullable', 'url', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['exists:business_categories,id'],
        ]);

        $organization->fill(collect($validated)->except(['latitude', 'longitude', 'category_ids'])->toArray());

        if (! empty($validated['latitude']) && ! empty($validated['longitude'])) {
            // Mismo patrón de escritura que Business (ver HasGeoLocation): nunca
            // asignar lat/lng directo a la columna, siempre vía pointExpression().
            $organization->location = Organization::pointExpression($validated['latitude'], $validated['longitude']);
        }

        $organization->save();

        if (isset($validated['category_ids'])) {
            $organization->categories()->sync($validated['category_ids']);
        }

        return redirect()->route('admin.organizaciones.index')
            ->with('success', "{$organization->name} actualizado.");
    }
}
