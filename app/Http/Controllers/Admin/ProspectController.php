<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Prospect;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProspectController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Prospect::class);

        $status = $request->query('status', 'all');

        $prospects = Prospect::when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(20);

        return Inertia::render('Admin/Prospectos/Index', [
            'prospects' => $prospects,
            'activeStatus' => $status,
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Prospect::class);

        return Inertia::render('Admin/Prospectos/Form', ['prospect' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Prospect::class);

        $prospect = Prospect::create($this->validated($request));

        return redirect()->route('admin.prospectos.index')->with('success', "{$prospect->business_name} agregado.");
    }

    public function edit(Prospect $prospect): Response
    {
        $this->authorize('update', $prospect);

        return Inertia::render('Admin/Prospectos/Form', ['prospect' => $prospect]);
    }

    public function update(Request $request, Prospect $prospect): RedirectResponse
    {
        $this->authorize('update', $prospect);

        $prospect->update($this->validated($request));

        return redirect()->route('admin.prospectos.index')->with('success', "{$prospect->business_name} actualizado.");
    }

    public function destroy(Prospect $prospect): RedirectResponse
    {
        $this->authorize('delete', $prospect);

        $name = $prospect->business_name;
        $prospect->delete();

        return redirect()->route('admin.prospectos.index')->with('success', "{$name} eliminado.");
    }

    public function convert(Prospect $prospect): RedirectResponse
    {
        $this->authorize('update', $prospect);

        if ($prospect->organization_id) {
            return back()->with('success', "{$prospect->business_name} ya tiene una organización vinculada.");
        }

        $organization = $prospect->convertToOrganization();
        $prospect->update(['status' => 'fundador']);

        return redirect()->route('admin.organizaciones.edit', $organization)
            ->with('success', "{$prospect->business_name} convertido en organización GO Pro — completá su perfil.");
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'business_name' => ['required', 'string', 'max:255'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'territory' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'instagram' => ['nullable', 'string', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            'whatsapp' => ['nullable', 'string', 'max:30'],
            'observed_problem' => ['nullable', 'string', 'max:2000'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'source' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:' . implode(',', Prospect::STATUSES)],
            'last_contacted_at' => ['nullable', 'date'],
        ]);
    }
}
