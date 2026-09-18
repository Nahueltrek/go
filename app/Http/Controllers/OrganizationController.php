<?php

namespace App\Http\Controllers;

use App\Http\Resources\ExperienceResource;
use App\Http\Resources\OrganizationResource;
use App\Http\Resources\ProjectResource;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;

class OrganizationController extends Controller
{
    public function show(string $slug): Response
    {
        $organization = Organization::approved()
            ->withCoordinates()
            ->with(['commune.province.region', 'categories'])
            ->where('slug', $slug)
            ->firstOrFail();

        $organization->metricEvents()->create(['type' => 'visit']);

        return Inertia::render('Public/Operador', [
            'organization' => (new OrganizationResource($organization))->resolve(),
            'experiences' => ExperienceResource::collection(
                $organization->experiences()->published()->with(['images', 'activityType'])->get()
            ),
            'projects' => ProjectResource::collection(
                $organization->projects()->published()->with('images')->get()
            ),
        ]);
    }

    /**
     * Beacon de clics de contacto (GO_CHILE_MODELO_COMERCIAL.md §21), llamado
     * desde ContactButtons.vue con sendBeacon/fetch keepalive — sin sesión
     * Inertia, por eso responde 204 plano en vez de un JsonResponse.
     */
    public function trackClick(Request $request, string $slug): HttpResponse
    {
        $type = $request->input('type');

        if (! in_array($type, ['whatsapp_click', 'phone_click', 'website_click', 'instagram_click'], true)) {
            return response()->noContent(422);
        }

        $organization = Organization::approved()->where('slug', $slug)->first();

        if ($organization) {
            $organization->metricEvents()->create(['type' => $type]);
        }

        return response()->noContent();
    }
}
