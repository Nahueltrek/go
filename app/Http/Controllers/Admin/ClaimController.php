<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Claim;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClaimController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'pending');

        $claims = Claim::query()
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->with(['business', 'user'])
            ->latest()
            ->paginate(20)
            ->through(fn ($c) => [
                'id' => $c->id,
                'status' => $c->status,
                'evidence' => $c->evidence,
                'business' => $c->business->name,
                'business_slug' => $c->business->slug,
                'user' => $c->user->name,
                'user_email' => $c->user->email,
                'created_at' => $c->created_at->format('d-m-Y H:i'),
            ]);

        return Inertia::render('Admin/Claims', [
            'claims' => $claims,
            'status' => $status,
        ]);
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

        return back();
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

        return back();
    }
}
