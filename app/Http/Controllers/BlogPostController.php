<?php

namespace App\Http\Controllers;

use App\Http\Resources\BlogPostResource;
use App\Models\BlogPost;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BlogPostController extends Controller
{
    public function index(Request $request): Response
    {
        $category = $request->query('category');

        $posts = BlogPost::published()
            ->with(['author', 'relatedOrganization', 'relatedDestination'])
            ->when($category, fn ($q) => $q->where('category', $category))
            ->orderByDesc('published_at')
            ->paginate(12);

        return Inertia::render('Public/Bitacora', [
            'posts' => BlogPostResource::collection($posts),
            'activeCategory' => $category,
            'categories' => [
                ['key' => 'rutas', 'label' => 'Rutas'],
                ['key' => 'personas', 'label' => 'Personas'],
                ['key' => 'territorio', 'label' => 'Territorio'],
                ['key' => 'educacion', 'label' => 'Educación'],
                ['key' => 'conservacion', 'label' => 'Conservación'],
                ['key' => 'experiencias', 'label' => 'Experiencias'],
            ],
        ]);
    }

    public function show(string $slug): Response
    {
        $post = BlogPost::published()
            ->with(['author', 'relatedOrganization', 'relatedDestination'])
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
        ]);
    }
}
