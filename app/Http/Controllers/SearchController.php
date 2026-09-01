<?php

namespace App\Http\Controllers;

use App\Http\Resources\BusinessResource;
use App\Http\Resources\EventResource;
use App\Http\Resources\ExperienceResource;
use App\Http\Resources\OrganizationResource;
use App\Http\Resources\ProjectResource;
use App\Http\Resources\RouteResource;
use App\Models\Business;
use App\Models\BusinessCategory;
use App\Models\Commune;
use App\Models\Event;
use App\Models\Experience;
use App\Models\Organization;
use App\Models\Project;
use App\Models\Region;
use App\Models\Route;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SearchController extends Controller
{
    /**
     * Buscador global GO Chile — busca simultáneamente en experiences,
     * organizations y projects. routes y businesses (existentes de rm360)
     * quedan fuera de esta primera versión: se integran en una segunda
     * pasada cuando se defina si conviene unificarlos bajo "experiences"
     * o mantenerlos como tipos propios en el buscador.
     */
    public function index(Request $request): Response
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'type' => ['nullable', 'in:experiencia,operador,proyecto,negocio,ruta,evento'],
            'region_id' => ['nullable', 'integer', 'exists:regions,id'],
            'commune_id' => ['nullable', 'integer', 'exists:communes,id'],
            'activity' => ['nullable', 'string', 'exists:business_categories,slug'],
            'difficulty' => ['nullable', 'in:facil,medio,dificil,experto'],
            'price_max' => ['nullable', 'integer', 'min:0'],
        ]);

        $results = [
            'experiencias' => $this->searchExperiences($filters),
            'operadores' => $this->searchOrganizations($filters),
            'proyectos' => $this->searchProjects($filters),
            'negocios' => $this->searchBusinesses($filters),
            'rutas' => $this->searchRoutes($filters),
            'eventos' => $this->searchEvents($filters),
        ];

        // Si se pidió un tipo específico, se filtra la respuesta pero se
        // mantienen los otros arrays vacíos (no se recalculan) para que el
        // frontend pueda mostrar conteos consistentes con "todos" si el
        // usuario saca el filtro de tipo.
        if (! empty($filters['type'])) {
            $key = match ($filters['type']) {
                'experiencia' => 'experiencias',
                'operador' => 'operadores',
                'proyecto' => 'proyectos',
                'negocio' => 'negocios',
                'ruta' => 'rutas',
                'evento' => 'eventos',
            };
            $results = array_merge(
                ['experiencias' => [], 'operadores' => [], 'proyectos' => [], 'negocios' => [], 'rutas' => [], 'eventos' => []],
                [$key => $results[$key]]
            );
        }

        return Inertia::render('Public/Explorar', [
            'results' => $results,
            'filters' => $filters,
            'facets' => [
                'regions' => Region::orderBy('name')->get(['id', 'name', 'slug']),
                'activities' => BusinessCategory::orderBy('name')->get(['id', 'name', 'slug', 'icon']),
            ],
        ]);
    }

    private function searchExperiences(array $filters)
    {
        $query = Experience::query()
            ->published()
            ->withCoordinates()
            ->with(['organization', 'activityType', 'destination', 'images'])
            ->when($filters['q'] ?? null, fn ($q, $term) => $q->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%");
            }))
            ->when($filters['activity'] ?? null, fn ($q, $slug) => $q->whereHas(
                'activityType',
                fn ($q) => $q->where('slug', $slug)
            ))
            ->when($filters['difficulty'] ?? null, fn ($q, $d) => $q->where('difficulty', $d))
            ->when($filters['price_max'] ?? null, fn ($q, $max) => $q->where('price', '<=', $max))
            ->when($filters['commune_id'] ?? null, fn ($q, $id) => $q->whereHas(
                'organization',
                fn ($q) => $q->where('commune_id', $id)
            ))
            ->when($filters['region_id'] ?? null, fn ($q, $id) => $q->whereHas(
                'organization.commune.province',
                fn ($q) => $q->where('region_id', $id)
            ));

        return ExperienceResource::collection($query->orderByDesc('is_featured')->paginate(12));
    }

    private function searchOrganizations(array $filters)
    {
        $query = Organization::query()
            ->approved()
            ->withCoordinates()
            ->with(['commune', 'categories'])
            ->when($filters['q'] ?? null, fn ($q, $term) => $q->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%");
            }))
            ->when($filters['activity'] ?? null, fn ($q, $slug) => $q->whereHas(
                'categories',
                fn ($q) => $q->where('slug', $slug)
            ))
            ->when($filters['commune_id'] ?? null, fn ($q, $id) => $q->where('commune_id', $id))
            ->when($filters['region_id'] ?? null, fn ($q, $id) => $q->whereHas(
                'commune.province',
                fn ($q) => $q->where('region_id', $id)
            ));

        return OrganizationResource::collection($query->paginate(12));
    }

    private function searchProjects(array $filters)
    {
        $query = Project::query()
            ->published()
            ->withCoordinates()
            ->with(['organization', 'images'])
            ->when($filters['q'] ?? null, fn ($q, $term) => $q->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%");
            }))
            ->when($filters['commune_id'] ?? null, fn ($q, $id) => $q->whereHas(
                'organization',
                fn ($q) => $q->where('commune_id', $id)
            ));

        return ProjectResource::collection($query->paginate(12));
    }

    private function searchBusinesses(array $filters)
    {
        $query = Business::query()
            ->where('is_active', true)
            ->withCoordinates()
            ->with(['category', 'media'])
            ->when($filters['q'] ?? null, fn ($q, $term) => $q->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%");
            }))
            ->when($filters['activity'] ?? null, fn ($q, $slug) => $q->whereHas(
                'category',
                fn ($q) => $q->where('slug', $slug)
            ))
            ->when($filters['commune_id'] ?? null, fn ($q, $id) => $q->where('commune_id', $id));

        return BusinessResource::collection($query->paginate(12));
    }

    private function searchRoutes(array $filters)
    {
        $query = Route::query()
            ->with(['destination'])
            ->when($filters['q'] ?? null, fn ($q, $term) => $q->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%");
            }))
            ->when($filters['difficulty'] ?? null, fn ($q, $d) => $q->where('difficulty', $d));

        return RouteResource::collection($query->paginate(12));
    }

    private function searchEvents(array $filters)
    {
        $query = Event::query()
            ->published()
            ->upcoming()
            ->with(['organization', 'destination'])
            ->when($filters['q'] ?? null, fn ($q, $term) => $q->where(function ($q) use ($term) {
                $q->where('title', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%");
            }))
            ->when($filters['difficulty'] ?? null, fn ($q, $d) => $q->where('difficulty', $d));

        return EventResource::collection($query->paginate(12));
    }
}
