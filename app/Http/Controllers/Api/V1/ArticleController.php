<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ArticleResource;
use App\Models\Article;
use App\Models\Destination;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'destination' => 'nullable|string|exists:destinations,slug',
            'per_page' => 'nullable|integer|min:1|max:50',
        ]);

        $query = Article::query()->published()->with(['category', 'tags', 'author']);

        if (! empty($validated['destination'])) {
            $destination = Destination::where('slug', $validated['destination'])->firstOrFail();
            $query->where('destination_id', $destination->id);
        }

        return ArticleResource::collection(
            $query->latest('published_at')->paginate($validated['per_page'] ?? 20)
        );
    }

    public function show(string $slug)
    {
        $article = Article::query()
            ->published()
            ->where('slug', $slug)
            ->with(['category', 'tags', 'author'])
            ->firstOrFail();

        return new ArticleResource($article);
    }
}
