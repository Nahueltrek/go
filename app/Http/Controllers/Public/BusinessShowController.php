<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Business;
use Inertia\Inertia;

class BusinessShowController extends Controller
{
    public function __invoke(string $slug)
    {
        $business = Business::query()
            ->active()
            ->where('slug', $slug)
            ->with([
                'category',
                'commune',
                'contacts',
                'socials',
                'services',
                'reviews' => fn ($q) => $q->approved()->latest(),
            ])
            ->withCoordinates()
            ->firstOrFail();

        return Inertia::render('Public/BusinessShow', [
            'business' => [
                'id' => $business->id,
                'name' => $business->name,
                'slug' => $business->slug,
                'description' => $business->description,
                'address' => $business->address,
                'category' => $business->category?->name,
                'commune' => $business->commune?->name,
                'latitude' => $business->latitude ? (float) $business->latitude : null,
                'longitude' => $business->longitude ? (float) $business->longitude : null,
                'verification_status' => $business->verification_status,
                'claim_status' => $business->claim_status,
                'opening_hours' => $business->opening_hours,
                'contacts' => $business->contacts->map(fn ($c) => [
                    'phone' => $c->phone,
                    'whatsapp' => $c->whatsapp,
                    'email' => $c->email,
                    'website' => $c->website,
                ]),
                'socials' => $business->socials->map(fn ($s) => [
                    'platform' => $s->platform,
                    'url' => $s->url,
                ]),
                'services' => $business->services->map(fn ($s) => [
                    'name' => $s->name,
                    'description' => $s->description,
                ]),
                'reviews' => $business->reviews->map(fn ($r) => [
                    'rating' => $r->rating,
                    'comment' => $r->comment,
                    'created_at' => $r->created_at->toIso8601String(),
                ]),
            ],
        ]);
    }
}
