<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CollaborationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CollaborationRequestController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', CollaborationRequest::class);

        $status = $request->query('status', 'pending');

        $requests = CollaborationRequest::with(['region', 'commune'])
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(20);

        return Inertia::render('Admin/Colaboradores', [
            'requests' => $requests,
            'activeStatus' => $status,
        ]);
    }

    public function approve(CollaborationRequest $collaborationRequest): RedirectResponse
    {
        $this->authorize('approve', $collaborationRequest);

        // approve() ya crea la Organization y marca la solicitud como aprobada
        // — ver App\Models\CollaborationRequest::approve()
        $collaborationRequest->approve();

        return back()->with('success', "{$collaborationRequest->name} aprobado — perfil de organización creado.");
    }

    public function reject(CollaborationRequest $collaborationRequest): RedirectResponse
    {
        $this->authorize('reject', $collaborationRequest);

        $collaborationRequest->update(['status' => 'rejected']);

        return back()->with('success', "{$collaborationRequest->name} rechazado.");
    }
}
