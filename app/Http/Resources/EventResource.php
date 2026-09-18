<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'category' => $this->category,
            'starts_at' => $this->starts_at?->toIso8601String(),
            'ends_at' => $this->ends_at?->toIso8601String(),
            'capacity' => $this->capacity,
            'price' => $this->price,
            'difficulty' => $this->difficulty,
            'cover_image' => $this->cover_image,
            // organization_id es nullable (eventos editoriales de GO Chile,
            // sin operador atribuido) — $this->organization es null en ese
            // caso, y OrganizationResource(null) rompe al leer sus propiedades.
            'organization' => $this->whenLoaded('organization', fn () => $this->organization ? new OrganizationResource($this->organization) : null),
            'destination' => $this->whenLoaded('destination', fn () => [
                'id' => $this->destination->id,
                'name' => $this->destination->name,
            ]),
        ];
    }
}
