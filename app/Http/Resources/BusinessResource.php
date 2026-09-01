<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * VERIFICADO contra app/Models/Business.php real (rm360.0km.app, subido por
 * el usuario): category() y media() existen tal cual, con esos nombres.
 * media() es morphMany(Media::class, 'mediable'), igual que se asumió acá.
 */
class BusinessResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'category' => $this->whenLoaded('category', fn () => $this->category->name),
            'images' => $this->whenLoaded('media', fn () => $this->media->pluck('url')),
            'location' => $this->when($this->latitude !== null, fn () => [
                'lat' => $this->latitude,
                'lng' => $this->longitude,
            ]),
        ];
    }
}
