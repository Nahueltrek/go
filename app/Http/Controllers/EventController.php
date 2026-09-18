<?php

namespace App\Http\Controllers;

use App\Http\Resources\EventResource;
use App\Models\Event;
use Inertia\Inertia;
use Inertia\Response;

class EventController extends Controller
{
    /**
     * Agenda GO Chile — listado completo, no solo lo que trae el buscador.
     */
    public function index(): Response
    {
        $events = Event::published()->upcoming()
            ->with(['organization', 'destination'])
            ->paginate(20);

        return Inertia::render('Public/Agenda', [
            'events' => EventResource::collection($events),
        ]);
    }

    public function show(string $slug): Response
    {
        $event = Event::published()
            ->with(['organization.commune', 'destination', 'images'])
            ->where('slug', $slug)
            ->firstOrFail();

        return Inertia::render('Public/Evento', [
            'event' => (new EventResource($event))->resolve(),
        ]);
    }
}
