<?php

namespace App\Http\Controllers;

use App\Http\Resources\BlogPostResource;
use App\Http\Resources\ProjectResource;
use App\Models\BlogPost;
use App\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BlogPostController extends Controller
{
    public function index(Request $request): Response
    {
        $category = $request->query('category');

        $categories = collect(BlogPost::CATEGORIES)
            ->map(fn ($label, $key) => ['key' => $key, 'label' => $label])
            ->values();

        // Vista filtrada por categoría: grilla simple paginada. No se creó
        // una ruta /bitacora/{categoria} nueva — se reutiliza el filtro por
        // query string que ya existía, que es la solución mínima coherente
        // con la arquitectura actual.
        if ($category) {
            $posts = BlogPost::published()
                ->with(['author', 'relatedOrganization', 'relatedDestination'])
                ->where('category', $category)
                ->orderByDesc('published_at')
                ->paginate(12)
                ->withQueryString();

            return Inertia::render('Public/Bitacora', [
                'mode' => 'category',
                'activeCategory' => $category,
                'categories' => $categories,
                'posts' => BlogPostResource::collection($posts),
            ]);
        }

        // Portada editorial: secciones curadas en vez de un listado infinito.
        $latest = BlogPost::published()
            ->with(['author', 'relatedOrganization', 'relatedDestination'])
            ->orderByDesc('published_at')
            ->limit(4)
            ->get();

        $personas = BlogPost::published()
            ->where('category', 'personas')
            ->orderByDesc('published_at')
            ->limit(4)
            ->get();

        $educacion = BlogPost::published()
            ->where('category', 'educacion')
            ->orderByDesc('published_at')
            ->limit(4)
            ->get();

        $proyectos = Project::published()
            ->with(['organization', 'images'])
            ->limit(3)
            ->get();

        return Inertia::render('Public/Bitacora', [
            'mode' => 'home',
            'activeCategory' => null,
            'categories' => $categories,
            'featured' => $latest->first() ? new BlogPostResource($latest->first()) : null,
            'secondary' => BlogPostResource::collection($latest->slice(1, 3)->values()),
            'personas' => BlogPostResource::collection($personas),
            'educacion' => BlogPostResource::collection($educacion),
            'proyectos' => ProjectResource::collection($proyectos),
        ]);
    }

    public function show(string $slug): Response
    {
        $post = BlogPost::published()
            ->with([
                'author', 'relatedOrganization', 'relatedDestination',
                'relatedRoute', 'relatedExperience', 'relatedProject',
            ])
            ->where('slug', $slug)
            ->firstOrFail();

        // Relacionados: mismos categoría, excluyendo el actual
        $related = BlogPost::published()
            ->where('category', $post->category)
            ->where('id', '!=', $post->id)
            ->limit(3)
            ->get();

        return Inertia::render('Public/BitacoraPost', [
            'post' => new BlogPostResource($post),
            'related' => BlogPostResource::collection($related),
            'categoryLabel' => BlogPost::CATEGORIES[$post->category] ?? $post->category,
        ]);
    }
}
