<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Claim;
use Illuminate\Http\Request;

class ClaimController extends Controller
{
    public function store(Request $request, string $slug)
    {
        $business = Business::where('slug', $slug)->firstOrFail();

        $this->authorize('claim', $business);

        $validated = $request->validate([
            'evidence' => 'nullable|string|max:2000',
        ]);

        Claim::create([
            'business_id' => $business->id,
            'user_id' => $request->user()->id,
            'status' => 'pending',
            'evidence' => $validated['evidence'] ?? null,
        ]);

        $business->update(['claim_status' => 'pending']);

        return back()->with('status', 'Solicitud enviada. Un administrador la va a revisar.');
    }
}
