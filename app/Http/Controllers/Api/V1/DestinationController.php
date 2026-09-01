<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\DestinationResource;
use App\Models\Destination;

class DestinationController extends Controller
{
    public function show(string $slug)
    {
        $destination = Destination::active()->where('slug', $slug)->firstOrFail();

        return new DestinationResource($destination);
    }
}
