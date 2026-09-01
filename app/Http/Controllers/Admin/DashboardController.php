<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Claim;
use App\Models\Review;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __invoke()
    {
        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'businesses_active' => Business::active()->count(),
                'reviews_pending' => Review::where('status', 'pending')->count(),
                'claims_pending' => Claim::where('status', 'pending')->count(),
            ],
        ]);
    }
}
