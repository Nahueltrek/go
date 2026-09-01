<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Inertia\Inertia;

class BlogIndexController extends Controller
{
    public function __invoke()
    {
        $articles = Article::query()
            ->published()
            ->with(['author', 'tags'])
            ->latest('published_at')
            ->get();

        return Inertia::render('Public/Blog', [
            'articles' => $articles->map(fn ($a) => [
                'title' => $a->title,
                'slug' => $a->slug,
                'excerpt' => $a->excerpt,
                'cover_image' => $a->cover_image,
                'author' => $a->author?->name,
                'published_at' => $a->published_at?->format('d-m-Y'),
                'tags' => $a->tags->pluck('name'),
            ]),
        ]);
    }
}
