<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExperienceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'difficulty' => $this->difficulty,
            'duration_minutes' => $this->duration_minutes,
            'capacity' => $this->capacity,
            'price' => $this->price,
            'cover_image' => $this->cover_image,
            'is_featured' => $this->is_featured,
            'activity_type' => $this->whenLoaded('activityType', fn () => $this->activityType->name),
            'organization' => $this->whenLoaded('organization', fn () => (new OrganizationResource($this->organization))->resolve()),
            'destination' => $this->whenLoaded('destination', fn () => [
                'id' => $this->destination->id,
                'name' => $this->destination->name,
                'slug' => $this->destination->slug,
            ]),
            'images' => $this->whenLoaded('images', fn () => $this->images->pluck('url')),
            'location' => $this->when($this->latitude !== null, fn () => [
                'lat' => $this->latitude,
                'lng' => $this->longitude,
            ]),
        ];
    }
}
