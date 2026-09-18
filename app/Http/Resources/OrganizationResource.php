<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrganizationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'name' => $this->name,
            'slug' => $this->slug,
            'status' => $this->status,
            'user_id' => $this->user_id,
            'owner' => $this->whenLoaded('user', fn () => $this->user ? [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
            ] : null),
            'verification_status' => $this->verification_status,
            'claim_status' => $this->claim_status,
            'plan' => $this->plan,
            'opening_hours' => $this->opening_hours,
            'description' => $this->description,
            'commune' => $this->whenLoaded('commune', fn () => [
                'id' => $this->commune->id,
                'name' => $this->commune->name,
                'region' => $this->commune->relationLoaded('province') && $this->commune->province?->relationLoaded('region')
                    ? $this->commune->province->region?->name
                    : null,
            ]),
            'categories' => $this->whenLoaded('categories', fn () => $this->categories->pluck('name')),
            'instagram' => $this->instagram,
            'website' => $this->website,
            'whatsapp' => $this->whatsapp,
            'logo_url' => $this->logo_url,
            'cover_image' => $this->cover_image,
            // Requiere que el controller haya encadenado ->withCoordinates()
            // en la query — la columna 'location' es WKB binario crudo, no
            // legible directo desde PHP (ver HasGeoLocation::scopeWithCoordinates).
            'location' => $this->when($this->latitude !== null, fn () => [
                'lat' => $this->latitude,
                'lng' => $this->longitude,
            ]),
        ];
    }
}
