<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, string $slug)
    {
        $business = Business::where('slug', $slug)->firstOrFail();

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:2000',
        ]);

        // unique(business_id, user_id) en la migración evita reviews
        // duplicadas del mismo usuario — acá lo traducimos a un mensaje
        // claro en vez de dejar que reviente como error 500 de integridad.
        if (Review::where('business_id', $business->id)->where('user_id', $request->user()->id)->exists()) {
            return response()->json(['message' => 'Ya dejaste una reseña para este negocio.'], 422);
        }

        $review = Review::create([
            'business_id' => $business->id,
            'user_id' => $request->user()->id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
            'status' => 'pending', // moderación antes de publicarse
        ]);

        return response()->json($review, 201);
    }
}
