<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Resources\ExperienceResource;
use App\Http\Resources\OrganizationResource;
use App\Http\Resources\ProjectResource;
use App\Models\Organization;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrganizationController extends Controller
{
    /**
     * "MI ORGANIZACIÓN" — panel principal del Owner (Sprint 2). Asume una
     * sola Organization por usuario para el MVP (ver
     * Organization::isOwnedBy() y docs/SPRINT_1_ARQUITECTURA_GO_CHILE.md §9
     * para la evolución futura a multi-owner).
     */
    public function show(Request $request): Response
    {
        $organization = $this->organizationFor($request);

        $organization->load(['commune.province.region', 'categories']);

        return Inertia::render('Owner/Organization/Show', [
            'organization' => (new OrganizationResource($organization))->resolve(),
            'experiences' => ExperienceResource::collection(
                $organization->experiences()->with('activityType')->latest()->get()
            ),
            'projects' => ProjectResource::collection(
                $organization->projects()->latest()->get()
            ),
        ]);
    }

    public function edit(Request $request): Response
    {
        $organization = $this->organizationFor($request);
        $this->authorize('update', $organization);

        $organization = Organization::withCoordinates()
            ->with(['commune.province.region', 'categories'])
            ->findOrFail($organization->id);

        return Inertia::render('Owner/Organization/Edit', [
            'organization' => (new OrganizationResource($organization))->resolve()
                + ['category_ids' => $organization->categories->pluck('id')],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $organization = $this->organizationFor($request);
        $this->authorize('update', $organization);

        // El Owner solo toca presentación/contacto — nunca status,
        // verification_status, claim_status ni user_id (esos quedan
        // exclusivamente en Admin\OrganizationController).
        $validated = $request->validate([
            'description' => ['nullable', 'string', 'max:2000'],
            'instagram' => ['nullable', 'string', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            'whatsapp' => ['nullable', 'string', 'max:30'],
            'logo_url' => ['nullable', 'url', 'max:255'],
            'cover_image' => ['nullable', 'url', 'max:255'],
        ]);

        $organization->update($validated);

        return redirect()->route('owner.organization.show')
            ->with('success', 'Organización actualizada.');
    }

    private function organizationFor(Request $request): Organization
    {
        return $request->user()->organizations()->firstOrFail();
    }
}
