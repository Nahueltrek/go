<?php

namespace Database\Seeders;

use App\Models\Destination;
use App\Models\Event;
use Illuminate\Database\Seeder;

/**
 * Primer evento de agenda para el Eclipse Solar Anular del 6 de febrero de
 * 2027. Contenido editorial de GO Chile (organization_id null a propósito):
 * ningún prospecto de FutaleufuAttractionsSeeder/ProspectosEclipse2027Seeder
 * confirmó todavía su participación como fundador, así que este evento no
 * se le atribuye a ningún operador puntual — es la ficha general del
 * fenómeno, sobre la que después se pueden enlazar experiencias de
 * operadores reales una vez conviertan de prospecto a organización.
 *
 * Horario de anularidad verificado contra fuentes públicas (Sky Live,
 * timeanddate.com) — no estimado.
 */
class EclipseFutaleufu2027EventSeeder extends Seeder
{
    public function run(): void
    {
        $destination = Destination::where('slug', 'reserva-nacional-futaleufu')->first();

        if (! $destination) {
            $this->command?->error('No existe el destino reserva-nacional-futaleufu — abortando seeder.');
            return;
        }

        $event = Event::firstOrNew(['slug' => 'eclipse-solar-anular-2027-futaleufu']);
        $event->fill([
            'destination_id' => $destination->id,
            'organization_id' => null,
            'title' => 'Eclipse Solar Anular 2027 — Futaleufú',
            'description' => "El 6 de febrero de 2027 Chile será escenario de un eclipse solar anular visible en todo el país. Futaleufú tiene la mayor duración de anularidad de Chile: 7 minutos y 29 segundos de \"anillo de fuego\", con el máximo alrededor de las 11:51 hrs (fase de anularidad aprox. 11:46–11:59 hrs, hora local — el horario exacto varía según el punto de observación).\n\nEs uno de los mejores puntos del país para verlo, junto a Chaitén, Quellón y Palena. La demanda de alojamiento en la zona va a ser alta con más de un año de anticipación — reservar con tiempo es clave.\n\nGO Chile está armando la agenda de operadores locales (rafting, trekking, alojamiento y gastronomía) que van a sumarse con experiencias específicas para el eclipse. Esta ficha se va a ir completando a medida que se confirmen.",
            'category' => 'turismo',
            'starts_at' => '2027-02-06 10:30:00',
            'ends_at' => '2027-02-06 12:30:00',
            'status' => 'published',
        ]);
        $event->location = Event::pointExpression(-43.1833, -71.8667);
        $event->save();
    }
}
