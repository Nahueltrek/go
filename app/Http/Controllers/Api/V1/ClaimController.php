<?php

namespace App\Http\Controllers\Api\V1;

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

        $claim = Claim::create([
            'business_id' => $business->id,
            'user_id' => $request->user()->id,
            'status' => 'pending',
            'evidence' => $validated['evidence'] ?? null,
        ]);

        $business->update(['claim_status' => 'pending']);

        return response()->json($claim, 201);
    }

    public function approve(Request $request, Claim $claim)
    {
        $this->authorize('reviewClaim', $claim->business);

        $claim->update([
            'status' => 'approved',
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        $claim->business->update([
            'claim_status' => 'claimed',
            'owner_id' => $claim->user_id,
        ]);

        return response()->json($claim->fresh());
    }

    public function reject(Request $request, Claim $claim)
    {
        $this->authorize('reviewClaim', $claim->business);

        $claim->update([
            'status' => 'rejected',
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        $claim->business->update(['claim_status' => 'unclaimed']);

        return response()->json($claim->fresh());
    }
}
