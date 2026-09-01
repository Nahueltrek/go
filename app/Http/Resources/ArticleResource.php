<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'body' => $this->when($request->routeIs('*.show'), $this->body),
            'cover_image' => $this->cover_image,
            'category' => $this->whenLoaded('category', fn () => $this->category->name),
            'tags' => $this->whenLoaded('tags', fn () => $this->tags->pluck('name')),
            'author' => $this->whenLoaded('author', fn () => $this->author->name),
            'published_at' => $this->published_at?->toIso8601String(),
        ];
    }
}
