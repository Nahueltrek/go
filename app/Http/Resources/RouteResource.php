<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * VERIFICADO contra app/Models/Route.php real: name, slug, description,
 * distance_km, duration_minutes, difficulty, destination() — todo coincide
 * exactamente. Se agrega cover_image, que estaba en el fillable real y no
 * se había expuesto acá.
 */
class RouteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'cover_image' => $this->cover_image,
            'difficulty' => $this->difficulty,
            'distance_km' => $this->distance_km,
            'duration_minutes' => $this->duration_minutes,
            'destination' => $this->whenLoaded('destination', fn () => [
                'id' => $this->destination->id,
                'name' => $this->destination->name,
            ]),
        ];
    }
}
