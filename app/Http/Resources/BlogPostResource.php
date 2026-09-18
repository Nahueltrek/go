<?php

namespace App\Http\Resources;

use App\Models\BlogPost;
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
            'category_label' => BlogPost::CATEGORIES[$this->category] ?? $this->category,
            'cover_image_url' => $this->cover_image_url,
            'author' => $this->whenLoaded('author', fn () => $this->author ? [
                'id' => $this->author->id,
                'name' => $this->author->name,
            ] : null),
            'published_at' => $this->published_at?->toDateString(),
            'related_organization' => $this->whenLoaded('relatedOrganization', fn () =>
                $this->relatedOrganization ? new OrganizationResource($this->relatedOrganization) : null
            ),
            'related_destination' => $this->whenLoaded('relatedDestination', fn () =>
                $this->relatedDestination ? new DestinationResource($this->relatedDestination) : null
            ),
            'related_route' => $this->whenLoaded('relatedRoute', fn () =>
                $this->relatedRoute ? new RouteResource($this->relatedRoute) : null
            ),
            'related_experience' => $this->whenLoaded('relatedExperience', fn () =>
                $this->relatedExperience ? new ExperienceResource($this->relatedExperience) : null
            ),
            'related_project' => $this->whenLoaded('relatedProject', fn () =>
                $this->relatedProject ? new ProjectResource($this->relatedProject) : null
            ),
        ];
    }
}
