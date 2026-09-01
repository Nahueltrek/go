<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityResource extends JsonResource
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
            'business' => $this->whenLoaded('business', fn () => $this->business?->name),
            'latitude' => $this->when(isset($this->latitude), fn () => (float) $this->latitude),
            'longitude' => $this->when(isset($this->longitude), fn () => (float) $this->longitude),
        ];
    }
}
