<?php

namespace Database\Seeders;

use App\Models\Attraction;
use App\Models\Commune;
use App\Models\Destination;
use Illuminate\Database\Seeder;

/**
 * Primeros 2 atractivos reales de Futaleufú, con coordenadas verificadas
 * contra fuente pública (no estimadas) — parte del trabajo de atractivos
 * para el Eclipse Solar Anular 2027 (ver ProspectosEclipse2027Seeder).
 *
 * Quedan afuera a propósito los miradores puntuales (Piedra del Águila,
 * Piedra Ventosa, Cerro La Bandera) porque no se pudo verificar su
 * coordenada exacta desde este entorno — agregarlos requiere confirmar
 * lat/lng contra Wikiloc/Andeshandbook antes de cargarlos (nunca inventar
 * coordenadas de un lugar real).
 */
class FutaleufuAttractionsSeeder extends Seeder
{
    public function run(): void
    {
        // La comuna Futaleufú (id conocido en producción) no estaba
        // vinculada a este destino todavía.
        $destination = Destination::where('slug', 'reserva-nacional-futaleufu')->first();
        $communeId = Commune::where('slug', 'futaleufu')->value('id')
            ?? Commune::where('name', 'Futaleufú')->value('id');

        if (! $destination) {
            $this->command?->error('No existe el destino reserva-nacional-futaleufu — abortando seeder.');
            return;
        }

        if ($communeId && ! $destination->commune_id) {
            $destination->update(['commune_id' => $communeId]);
        }

        $attractions = [
            [
                'name' => 'Reserva Nacional Futaleufú',
                'slug' => 'reserva-nacional-futaleufu-conaf',
                'description' => 'Área silvestre protegida de 120,65 km² administrada por CONAF, a 11 km de Futaleufú. Bosque templado lluvioso con ciprés de la cordillera, coigüe de Magallanes y lenga; hábitat de pumas, güiñas, huemules y cóndor. Parte de la Reserva de la Biósfera Bosques Templados Lluviosos de los Andes Australes.',
                'category' => 'reserva natural',
                'lat' => -43.283,
                'lng' => -71.800,
                'source' => 'admin',
                'source_url' => 'https://www.conaf.cl/parque_nacionales/reserva-nacional-futaleufu/',
                'source_type' => 'conaf',
            ],
            [
                'name' => 'Futaleufú — confluencia ríos Espolón y Futaleufú',
                'slug' => 'futaleufu-confluencia-rios',
                'description' => 'El pueblo de Futaleufú está ubicado en la confluencia de los valles de los ríos Espolón y Futaleufú, a 358 msnm y unos 10 km de la frontera argentina. Punto de partida habitual para las excursiones de rafting, kayak y trekking de la zona, y base para la observación del Eclipse Solar Anular del 6 de febrero de 2027 (Futaleufú está dentro de la franja de anularidad).',
                'category' => 'pueblo / punto de partida',
                'lat' => -43.1833,
                'lng' => -71.8667,
                'source' => 'admin',
                'source_url' => 'https://www.wikidata.org/wiki/Q13060',
                'source_type' => 'wikidata',
            ],
        ];

        foreach ($attractions as $data) {
            // firstOrNew (no updateOrCreate): 'location' es NOT NULL en la
            // tabla y updateOrCreate() dispara el INSERT antes de que
            // podamos asignarla — con firstOrNew se arma el modelo completo
            // en memoria, incluida location, y se persiste en un solo save().
            $attraction = Attraction::firstOrNew(['slug' => $data['slug']]);
            $attraction->fill([
                'destination_id' => $destination->id,
                'commune_id' => $communeId,
                'name' => $data['name'],
                'description' => $data['description'],
                'category' => $data['category'],
                'source' => $data['source'],
                'source_url' => $data['source_url'],
                'source_type' => $data['source_type'],
            ]);
            $attraction->location = Attraction::pointExpression($data['lat'], $data['lng']);
            $attraction->save();
        }
    }
}
