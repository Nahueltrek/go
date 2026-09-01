<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BlogPostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'content' => $this->when($request->routeIs('*.show'), $this->content),
            'category' => $this->category,
            'cover_image_url' => $this->cover_image_url,
            'published_at' => $this->published_at?->toDateString(),
            'related_organization' => $this->whenLoaded('relatedOrganization', fn () =>
                $this->relatedOrganization ? new OrganizationResource($this->relatedOrganization) : null
            ),
        ];
    }
}
