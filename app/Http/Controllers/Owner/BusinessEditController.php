<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessContact;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BusinessEditController extends Controller
{
    public function edit(Request $request, string $slug)
    {
        $business = Business::where('slug', $slug)->firstOrFail();

        $this->authorize('update', $business);

        $business->load('contacts');
        $contact = $business->contacts->first();

        return Inertia::render('Dashboard/Edit', [
            'business' => [
                'name' => $business->name,
                'slug' => $business->slug,
                'description' => $business->description,
                'address' => $business->address,
            ],
            'contact' => [
                'phone' => $contact?->phone,
                'whatsapp' => $contact?->whatsapp,
                'email' => $contact?->email,
                'website' => $contact?->website,
            ],
        ]);
    }

    public function update(Request $request, string $slug)
    {
        $business = Business::where('slug', $slug)->firstOrFail();

        $this->authorize('update', $business);

        $validated = $request->validate([
            'description' => 'nullable|string',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:30',
            'whatsapp' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
        ]);

        // El dueño solo puede tocar presentación (descripción, dirección,
        // contacto) — nunca name/slug/category/campos SERNATUR, tal como
        // se definió en BusinessPolicy y en el plan maestro original.
        $business->update([
            'description' => $validated['description'] ?? $business->description,
            'address' => $validated['address'] ?? $business->address,
        ]);

        BusinessContact::updateOrCreate(
            ['business_id' => $business->id],
            [
                'phone' => $validated['phone'] ?? null,
                'whatsapp' => $validated['whatsapp'] ?? null,
                'email' => $validated['email'] ?? null,
                'website' => $validated['website'] ?? null,
            ]
        );

        return back()->with('status', 'Cambios guardados.');
    }
}
