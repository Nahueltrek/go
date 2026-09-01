<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CajonDelMaipoDestinationSeeder extends Seeder
{
    // Este seeder es el ÚNICO lugar donde "Cajón del Maipo" aparece como dato,
    // nunca como código hardcodeado en el núcleo del sistema — así se cumple
    // el principio de arquitectura multi-destino.
    public function run(): void
    {
        DB::table('destinations')->updateOrInsert(
            ['slug' => 'cajon-del-maipo'],
            [
                'name' => 'Cajón del Maipo',
                'description' => 'Destino piloto de Ruta 360 — montaña, termas, trekking y gastronomía en la precordillera de la Región Metropolitana.',
                'is_active' => true,
                'active_layers' => json_encode([
                    'alojamiento', 'gastronomia', 'trekking', 'camping', 'termas',
                    'atractivos', 'transporte', 'turismo_aventura', 'cultura', 'comercio', 'eventos',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $categories = [
            ['name' => 'Alojamiento', 'slug' => 'alojamiento', 'map_layer' => 'alojamiento'],
            ['name' => 'Gastronomía', 'slug' => 'gastronomia', 'map_layer' => 'gastronomia'],
            ['name' => 'Trekking', 'slug' => 'trekking', 'map_layer' => 'trekking'],
            ['name' => 'Camping', 'slug' => 'camping', 'map_layer' => 'camping'],
            ['name' => 'Termas', 'slug' => 'termas', 'map_layer' => 'termas'],
            ['name' => 'Turismo aventura', 'slug' => 'turismo-aventura', 'map_layer' => 'turismo_aventura'],
            ['name' => 'Transporte', 'slug' => 'transporte', 'map_layer' => 'transporte'],
            ['name' => 'Cultura', 'slug' => 'cultura', 'map_layer' => 'cultura'],
            ['name' => 'Comercio', 'slug' => 'comercio', 'map_layer' => 'comercio'],
        ];

        foreach ($categories as $category) {
            DB::table('business_categories')->updateOrInsert(
                ['slug' => $category['slug']],
                array_merge($category, ['created_at' => now(), 'updated_at' => now()])
            );
        }
    }
}
