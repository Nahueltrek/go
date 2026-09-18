<?php

namespace Database\Seeders;

use App\Models\Attraction;
use App\Models\Commune;
use App\Models\Destination;
use Illuminate\Database\Seeder;

/**
 * GO_CHILE_ECLIPSE_2027_MIRADORES.md/.csv — 3 miradores de Futaleufú con
 * coordenadas verificadas en cartografía pública (OpenStreetMap/Mapcarta),
 * complemento de FutaleufuAttractionsSeeder para la observación del
 * Eclipse Solar Anular del 6 de febrero de 2027.
 *
 * "Torre de Agua / Mirador Laguna Espejo" queda afuera a propósito — la
 * fuente marca su coordenada como no verificada, mismo criterio de no
 * cargar puntos sin confirmar que ya se usó en el resto del proyecto.
 *
 * Estos 3 sí tienen coordenada verificada, pero la fuente pide validar
 * acceso/sendero/condiciones en terreno antes de tratarlos como puntos
 * oficiales de una experiencia — por eso quedan con esa advertencia
 * explícita en la descripción en vez de presentarse como confirmados al
 * 100%.
 */
class FutaleufuMiradoresEclipse2027Seeder extends Seeder
{
    public function run(): void
    {
        $destination = Destination::where('slug', 'reserva-nacional-futaleufu')->first();
        $communeId = Commune::where('slug', 'futaleufu')->value('id')
            ?? Commune::where('name', 'Futaleufú')->value('id');

        if (! $destination) {
            $this->command?->error('No existe el destino reserva-nacional-futaleufu — abortando seeder.');
            return;
        }

        $miradores = [
            [
                'name' => 'Piedra del Águila',
                'slug' => 'piedra-del-aguila-futaleufu',
                'lat' => -43.15823,
                'lng' => -71.89175,
                'description' => 'Mirador natural en Futaleufú, identificado en cartografía pública (OpenStreetMap/Mapcarta). Prioridad alta para la observación del Eclipse Solar Anular del 6 de febrero de 2027. Pendiente validar sendero, acceso y condiciones en terreno antes de tratarlo como punto oficial de una experiencia.',
            ],
            [
                'name' => 'Piedra Ventosa',
                'slug' => 'piedra-ventosa-reserva-futaleufu',
                'lat' => -43.19686,
                'lng' => -71.79391,
                'description' => 'Mirador dentro de la Reserva Nacional Futaleufú, identificado en cartografía pública (OpenStreetMap/Mapcarta). Prioridad alta para la observación del Eclipse Solar Anular del 6 de febrero de 2027. Pendiente confirmar acceso, sendero y condiciones de la Reserva antes de tratarlo como punto oficial de una experiencia.',
            ],
            [
                'name' => 'Cerro La Bandera',
                'slug' => 'cerro-la-bandera-futaleufu',
                'lat' => -43.17744,
                'lng' => -71.87492,
                'description' => 'Mirador en Futaleufú, identificado en cartografía pública (OpenStreetMap/Mapcarta). Prioridad alta para la observación del Eclipse Solar Anular del 6 de febrero de 2027. Pendiente verificar condiciones de acceso y eventual ingreso por propiedad privada antes de tratarlo como punto oficial de una experiencia.',
            ],
        ];

        foreach ($miradores as $data) {
            $attraction = Attraction::firstOrNew(['slug' => $data['slug']]);
            $attraction->fill([
                'destination_id' => $destination->id,
                'commune_id' => $communeId,
                'name' => $data['name'],
                'description' => $data['description'],
                'category' => 'mirador',
                'source' => 'admin',
                'source_url' => sprintf('https://www.google.com/maps/search/?api=1&query=%F,%F', $data['lat'], $data['lng']),
                'source_type' => 'openstreetmap',
            ]);
            $attraction->location = Attraction::pointExpression($data['lat'], $data['lng']);
            $attraction->save();
        }
    }
}
