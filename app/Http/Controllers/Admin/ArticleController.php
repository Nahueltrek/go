<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ArticleTag;
use App\Models\Destination;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ArticleController extends Controller
{
    public function index()
    {
        $articles = Article::query()
            ->with(['author'])
            ->latest()
            ->paginate(20)
            ->through(fn ($a) => [
                'id' => $a->id,
                'title' => $a->title,
                'slug' => $a->slug,
                'status' => $a->status,
                'author' => $a->author?->name,
                'published_at' => $a->published_at?->format('d-m-Y'),
            ]);

        return Inertia::render('Admin/Articles/Index', [
            'articles' => $articles,
        ]);
    }

    public function create()
    {
        $this->authorize('create', Article::class);

        return Inertia::render('Admin/Articles/Edit', [
            'article' => null,
            'canPublish' => request()->user()->hasRole('admin') || request()->user()->hasRole('super_admin'),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Article::class);

        $validated = $this->validated($request);

        $canPublish = $request->user()->hasRole('admin') || $request->user()->hasRole('super_admin');
        $status = ($validated['status'] === 'published' && $canPublish) ? 'published' : 'draft';

        $destination = Destination::where('slug', 'cajon-del-maipo')->firstOrFail();

        $article = Article::create([
            'destination_id' => $destination->id,
            'author_id' => $request->user()->id,
            'title' => $validated['title'],
            'slug' => $validated['slug'] ?: Str::slug($validated['title']),
            'excerpt' => $validated['excerpt'],
            'body' => $validated['body'],
            'status' => $status,
            'published_at' => $status === 'published' ? now() : null,
        ]);

        $this->syncTags($article, $validated['tags'] ?? '');

        return redirect()->route('admin.articles.index')->with('status', 'Artículo guardado.');
    }

    public function edit(Article $article)
    {
        $this->authorize('update', $article);

        return Inertia::render('Admin/Articles/Edit', [
            'article' => [
                'id' => $article->id,
                'title' => $article->title,
                'slug' => $article->slug,
                'excerpt' => $article->excerpt,
                'body' => $article->body,
                'status' => $article->status,
                'tags' => $article->tags->pluck('name')->join(', '),
            ],
            'canPublish' => request()->user()->hasRole('admin') || request()->user()->hasRole('super_admin'),
        ]);
    }

    public function update(Request $request, Article $article)
    {
        $this->authorize('update', $article);

        $validated = $this->validated($request, $article->id);

        $canPublish = $request->user()->hasRole('admin') || $request->user()->hasRole('super_admin');
        $wantsPublish = $validated['status'] === 'published';

        if ($wantsPublish) {
            $this->authorize('publish', $article);
        }

        $status = $wantsPublish ? 'published' : 'draft';

        $article->update([
            'title' => $validated['title'],
            'slug' => $validated['slug'] ?: Str::slug($validated['title']),
            'excerpt' => $validated['excerpt'],
            'body' => $validated['body'],
            'status' => $status,
            'published_at' => $status === 'published' ? ($article->published_at ?? now()) : null,
        ]);

        $this->syncTags($article, $validated['tags'] ?? '');

        return redirect()->route('admin.articles.index')->with('status', 'Artículo actualizado.');
    }

    protected function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:articles,slug' . ($ignoreId ? ",{$ignoreId},id" : ''),
            'excerpt' => 'nullable|string|max:500',
            'body' => 'nullable|string',
            'status' => 'required|in:draft,published',
            'tags' => 'nullable|string|max:500',
        ]);
    }

    protected function syncTags(Article $article, string $tagsCsv): void
    {
        $names = collect(explode(',', $tagsCsv))
            ->map(fn ($t) => trim($t))
            ->filter()
            ->unique();

        $tagIds = $names->map(function ($name) {
            $tag = ArticleTag::firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name]
            );

            return $tag->id;
        });

        $article->tags()->sync($tagIds);
    }
}
