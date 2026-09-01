<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'pending');

        $reviews = Review::query()
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->with(['business', 'user'])
            ->latest()
            ->paginate(20)
            ->through(fn ($r) => [
                'id' => $r->id,
                'rating' => $r->rating,
                'comment' => $r->comment,
                'status' => $r->status,
                'business' => $r->business->name,
                'business_slug' => $r->business->slug,
                'user' => $r->user->name,
                'created_at' => $r->created_at->format('d-m-Y H:i'),
            ]);

        return Inertia::render('Admin/Reviews', [
            'reviews' => $reviews,
            'status' => $status,
        ]);
    }

    public function approve(Review $review)
    {
        $review->update(['status' => 'approved']);

        return back();
    }

    public function reject(Review $review)
    {
        $review->update(['status' => 'rejected']);

        return back();
    }
}
