<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Business;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $businesses = Business::where('owner_id', $request->user()->id)->get();

        return Inertia::render('Dashboard/Index', [
            'businesses' => $businesses->map(fn ($b) => [
                'name' => $b->name,
                'slug' => $b->slug,
                'claim_status' => $b->claim_status,
                'verification_status' => $b->verification_status,
            ]),
        ]);
    }
}
