<?php

namespace Database\Seeders;

use App\Models\Destination;
use App\Models\Event;
use Illuminate\Database\Seeder;

/**
 * Cascada Trail 2026 — carrera de trail running real y con fecha
 * confirmada en el Santuario Cascada de las Ánimas, Cajón del Maipo
 * (fuente: tusdesafios.com/events/cascada-trail-2026). Primer evento de
 * agenda para el destino piloto de GO Chile, fuera del Eclipse 2027.
 *
 * Coordenadas: no se encontró el punto exacto del santuario en las
 * fuentes consultadas — se usa el centro del pueblo de San Alfonso
 * (33°43'41"S 70°19'02"W), donde está la entrada del santuario, en vez
 * de inventar una coordenada más precisa sin verificar.
 */
class CascadaTrail2026EventSeeder extends Seeder
{
    public function run(): void
    {
        $destination = Destination::where('slug', 'cajon-del-maipo')->first();

        if (! $destination) {
            $this->command?->error('No existe el destino cajon-del-maipo — abortando seeder.');
            return;
        }

        $event = Event::firstOrNew(['slug' => 'cascada-trail-2026']);
        $event->fill([
            'destination_id' => $destination->id,
            'organization_id' => null,
            'title' => 'Cascada Trail 2026',
            'description' => "Carrera de trail running en el Santuario Cascada de las Ánimas (San Alfonso, Cajón del Maipo), el sábado 3 de octubre de 2026. Distancias para todos los niveles: 30K, 21K, 12K, 6K y 3K trekking familiar. El evento promueve el turismo responsable y el cuidado del ecosistema del Cajón del Maipo, con senderos marcados y seguros.\n\nUbicación aproximada al centro de San Alfonso — el punto exacto de largada dentro del santuario se ajustará cuando se confirme.",
            'category' => 'salidas',
            'starts_at' => '2026-10-03 08:00:00',
            'status' => 'published',
        ]);
        $event->location = Event::pointExpression(-33.7281, -70.3172);
        $event->save();
    }
}
