<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'category' => $this->category,
            'how_to_collaborate' => $this->how_to_collaborate,
            'organization' => $this->whenLoaded('organization', fn () => (new OrganizationResource($this->organization))->resolve()),
            'images' => $this->whenLoaded('images', fn () => $this->images->pluck('url')),
        ];
    }
}
