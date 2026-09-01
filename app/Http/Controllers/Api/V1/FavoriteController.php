<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    protected array $allowedTypes = [
        'business' => \App\Models\Business::class,
        'attraction' => \App\Models\Attraction::class,
        'route' => \App\Models\Route::class,
    ];

    public function index(Request $request)
    {
        return response()->json(
            $request->user()->favorites()->latest()->paginate(20)
        );
    }

    public function toggle(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string|in:business,attraction,route',
            'id' => 'required|integer',
        ]);

        $modelClass = $this->allowedTypes[$validated['type']];
        $model = $modelClass::findOrFail($validated['id']);

        $existing = Favorite::where('user_id', $request->user()->id)
            ->where('favoritable_type', $validated['type'])
            ->where('favoritable_id', $model->id)
            ->first();

        if ($existing) {
            $existing->delete();

            return response()->json(['favorited' => false]);
        }

        Favorite::create([
            'user_id' => $request->user()->id,
            'favoritable_type' => $validated['type'],
            'favoritable_id' => $model->id,
        ]);

        return response()->json(['favorited' => true], 201);
    }
}
