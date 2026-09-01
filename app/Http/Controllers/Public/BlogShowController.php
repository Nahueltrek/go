<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Inertia\Inertia;

class BlogShowController extends Controller
{
    public function __invoke(string $slug)
    {
        $article = Article::query()
            ->published()
            ->where('slug', $slug)
            ->with(['author', 'tags', 'category'])
            ->firstOrFail();

        return Inertia::render('Public/BlogShow', [
            'article' => [
                'title' => $article->title,
                'body' => $article->body,
                'cover_image' => $article->cover_image,
                'author' => $article->author?->name,
                'published_at' => $article->published_at?->format('d-m-Y'),
                'category' => $article->category?->name,
                'tags' => $article->tags->pluck('name'),
            ],
        ]);
    }
}
