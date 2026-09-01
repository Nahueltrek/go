<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttractionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'cover_image' => $this->cover_image,
            'category' => $this->category,
            'commune' => $this->whenLoaded('commune', fn () => $this->commune->name),
            'latitude' => $this->when(isset($this->latitude), fn () => (float) $this->latitude),
            'longitude' => $this->when(isset($this->longitude), fn () => (float) $this->longitude),
        ];
    }
}
