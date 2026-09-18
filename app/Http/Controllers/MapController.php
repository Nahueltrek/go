<?php

namespace App\Http\Controllers;

use App\Models\Attraction;
use App\Models\Business;
use App\Models\Destination;
use App\Models\Event;
use App\Models\Experience;
use App\Models\Organization;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class MapController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Public/Mapa', [
            'layers' => $this->layerDefinitions(),
        ]);
    }

    /**
     * GeoJSON combinado de todas las capas con coordenadas.
     *
     * IMPORTANTE: 'location' en MariaDB es WKB binario crudo — no se puede
     * leer directo desde PHP. Cada query encadena ->withCoordinates() (de
     * HasGeoLocation), que agrega columnas planas `latitude`/`longitude`
     * al resultado vía ST_Y/ST_X. Sin ese scope, $model->latitude es null
     * y el registro se filtra silenciosamente del mapa.
     */
    public function geojson(Request $request): JsonResponse
    {
        $layer = $request->query('layer');

        $features = collect();

        if (! $layer || $layer === 'experiencias') {
            $features = $features->merge(
                Experience::published()->withCoordinates()->whereNotNull('location')
                    ->with('activityType')->get()
                    ->filter(fn ($e) => $e->latitude !== null)
                    ->map(fn ($e) => $this->feature(
                        $e->latitude, $e->longitude, 'experiencias', $e->name,
                        "/experiencias/{$e->slug}", $e->activityType?->name
                    ))
            );
        }

        if (! $layer || $layer === 'operadores') {
            $features = $features->merge(
                Organization::approved()->withCoordinates()->whereNotNull('location')->get()
                    ->filter(fn ($o) => $o->latitude !== null)
                    ->map(fn ($o) => $this->feature(
                        $o->latitude, $o->longitude, 'operadores', $o->name,
                        "/operadores/{$o->slug}", $o->type
                    ))
            );
        }

        if (! $layer || $layer === 'proyectos') {
            $features = $features->merge(
                Project::published()->withCoordinates()->whereNotNull('location')->get()
                    ->filter(fn ($p) => $p->latitude !== null)
                    ->map(fn ($p) => $this->feature(
                        $p->latitude, $p->longitude, 'proyectos', $p->name,
                        "/proyectos/{$p->slug}", $p->category
                    ))
            );
        }

        if (! $layer || $layer === 'negocios') {
            $features = $features->merge(
                Business::where('is_active', true)->withCoordinates()
                    ->with('category')->get()
                    ->filter(fn ($b) => $b->latitude !== null)
                    ->map(fn ($b) => $this->feature(
                        $b->latitude, $b->longitude, 'negocios', $b->name,
                        "/negocios/{$b->slug}", $b->category?->name
                    ))
            );
        }

        if (! $layer || $layer === 'atractivos') {
            $features = $features->merge(
                Attraction::withCoordinates()->whereNotNull('location')->get()
                    ->filter(fn ($a) => $a->latitude !== null)
                    ->map(fn ($a) => $this->feature(
                        $a->latitude, $a->longitude, 'atractivos', $a->name,
                        "/atractivos/{$a->slug}", $a->category
                    ))
            );
        }

        if (! $layer || $layer === 'destinos') {
            $features = $features->merge(
                Destination::withCoordinates()->whereNotNull('location')
                    ->where('is_active', true)->get()
                    ->filter(fn ($d) => $d->latitude !== null)
                    ->map(fn ($d) => $this->feature(
                        $d->latitude, $d->longitude, 'destinos', $d->name,
                        null, null
                    ))
            );
        }

        if (! $layer || $layer === 'eventos') {
            $features = $features->merge(
                Event::published()->upcoming()->withCoordinates()->whereNotNull('location')->get()
                    ->filter(fn ($e) => $e->latitude !== null)
                    ->map(fn ($e) => $this->feature(
                        $e->latitude, $e->longitude, 'eventos', $e->title,
                        "/agenda/{$e->slug}", $e->category
                    ))
            );
        }

        return response()->json([
            'type' => 'FeatureCollection',
            'features' => $features->values(),
        ]);
    }

    private function feature(float $lat, float $lng, string $layer, string $name, ?string $url, ?string $subtitle): array
    {
        return [
            'type' => 'Feature',
            'geometry' => [
                'type' => 'Point',
                'coordinates' => [$lng, $lat], // GeoJSON usa (lng, lat)
            ],
            'properties' => compact('layer', 'name', 'url', 'subtitle'),
        ];
    }

    private function layerDefinitions(): array
    {
        return [
            ['key' => 'experiencias', 'label' => 'Experiencias', 'color' => '#1c8c82', 'icon' => '🥾'],
            ['key' => 'operadores', 'label' => 'Operadores', 'color' => '#b4562a', 'icon' => '🤝'],
            ['key' => 'negocios', 'label' => 'Negocios', 'color' => '#7b5ea3', 'icon' => '🏪'],
            ['key' => 'proyectos', 'label' => 'Proyectos', 'color' => '#4a7c3f', 'icon' => '🌱'],
            ['key' => 'atractivos', 'label' => 'Atractivos', 'color' => '#c2410c', 'icon' => '📍'],
            ['key' => 'destinos', 'label' => 'Destinos', 'color' => '#0d9488', 'icon' => '🏞️'],
            ['key' => 'eventos', 'label' => 'Agenda', 'color' => '#c9962c', 'icon' => '📅'],
        ];
    }
}
