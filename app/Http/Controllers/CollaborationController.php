<?php

namespace App\Http\Controllers;

use App\Models\CollaborationRequest;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CollaborationController extends Controller
{
    /**
     * Formulario público "Quiero ser parte de GO Chile" — sin auth,
     * cualquiera puede postular. La aprobación es manual desde el admin.
     */
    public function create(): Response
    {
        return Inertia::render('Public/Colaboradores', [
            'regions' => Region::orderBy('name')->get(['id', 'name']),
            'types' => CollaborationRequest::TYPES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'in:guia,operador,agencia,emprendimiento,alojamiento,marca,proyecto,organizacion'],
            'name' => ['required', 'string', 'max:150'],
            'region_id' => ['nullable', 'exists:regions,id'],
            'commune_id' => ['nullable', 'exists:communes,id'],
            'instagram' => ['nullable', 'string', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            'whatsapp' => ['nullable', 'string', 'max:30'],
            'description' => ['nullable', 'string', 'max:2000'],
            'services' => ['nullable', 'array'],
        ]);

        CollaborationRequest::create($validated + ['status' => 'pending']);

        return back()->with('success', '¡Gracias! Revisamos tu postulación y te contactamos pronto.');
    }
}
