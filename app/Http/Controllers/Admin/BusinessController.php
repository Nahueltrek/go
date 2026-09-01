<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Business;
use Inertia\Inertia;

class BusinessController extends Controller
{
    public function index()
    {
        $businesses = Business::query()
            ->with(['category', 'commune', 'owner'])
            ->latest()
            ->paginate(20)
            ->through(fn ($b) => [
                'id' => $b->id,
                'name' => $b->name,
                'slug' => $b->slug,
                'category' => $b->category?->name,
                'commune' => $b->commune?->name,
                'is_active' => $b->is_active,
                'sernatur_status' => $b->sernatur_status,
                'verification_status' => $b->verification_status,
                'claim_status' => $b->claim_status,
                'owner' => $b->owner?->name,
                'source' => $b->source,
            ]);

        return Inertia::render('Admin/Businesses', [
            'businesses' => $businesses,
        ]);
    }
}
