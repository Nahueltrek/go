<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GoChileRealDataSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Trekking', 'slug' => 'trekking', 'map_layer' => 'trekking', 'icon' => 'trekking'],
            ['name' => 'Rafting', 'slug' => 'rafting', 'map_layer' => 'agua', 'icon' => 'agua'],
            ['name' => 'Canyoning', 'slug' => 'canyoning', 'map_layer' => 'agua', 'icon' => 'agua'],
            ['name' => 'Cuatrimotos', 'slug' => 'cuatrimotos', 'map_layer' => 'motor', 'icon' => 'motor'],
            ['name' => 'Astroturismo', 'slug' => 'astroturismo', 'map_layer' => 'naturaleza', 'icon' => 'naturaleza'],
            ['name' => 'Camping', 'slug' => 'camping', 'map_layer' => 'naturaleza', 'icon' => 'naturaleza'],
        ];
        foreach ($categories as $c) {
            DB::table('business_categories')->updateOrInsert(
                ['slug' => $c['slug']],
                array_merge($c, ['created_at' => now(), 'updated_at' => now()])
            );
        }
        echo "Categorias: OK\n";

        $communeId = DB::table('communes')->where('code', '13203')->value('id');
        DB::table('destinations')->updateOrInsert(
            ['slug' => 'cajon-del-maipo'],
            [
                'commune_id' => $communeId,
                'name' => 'Cajon del Maipo',
                'description' => 'Valle cordillerano a 1 hora de Santiago, con rios, glaciares y termas.',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
        $destinationId = DB::table('destinations')->where('slug', 'cajon-del-maipo')->value('id');
        echo "Destino: OK id=$destinationId\n";

        $operators = [
            [
                'type' => 'operador', 'name' => 'Natexplora', 'slug' => 'natexplora',
                'description' => 'Empresa de turismo aventura en el Cajon del Maipo, funciona todo el ano. Rafting, canyoning, trekking y astroturismo. Premiada varios anos en TripAdvisor.',
                'website' => 'https://natexplora.cl',
                'activities' => ['rafting', 'canyoning', 'trekking', 'astroturismo'],
                'experience' => ['name' => 'Rafting en el Rio Maipo', 'activity' => 'rafting', 'difficulty' => 'medio'],
            ],
            [
                'type' => 'operador', 'name' => 'Isoterma Turismo', 'slug' => 'isoterma-turismo',
                'description' => 'Mas de 16 anos operando en el Cajon del Maipo. Rafting, cuatrimotos, canyoning y trekking en un parque privado de 3 hectareas.',
                'website' => 'https://isoterma.cl',
                'activities' => ['rafting', 'cuatrimotos', 'canyoning', 'trekking'],
                'experience' => ['name' => 'Cuatrimotos Cajon del Maipo', 'activity' => 'cuatrimotos', 'difficulty' => 'facil'],
            ],
            [
                'type' => 'operador', 'name' => 'Ruta Vertical', 'slug' => 'ruta-vertical',
                'description' => 'Operador de turismo aventura del Cajon del Maipo, especializado en rafting y experiencias para grupos y empresas.',
                'website' => null,
                'activities' => ['rafting'],
                'experience' => ['name' => 'Rafting para grupos', 'activity' => 'rafting', 'difficulty' => 'medio'],
            ],
        ];

        foreach ($operators as $op) {
            DB::table('organizations')->updateOrInsert(
                ['slug' => $op['slug']],
                [
                    'type' => $op['type'],
                    'name' => $op['name'],
                    'description' => $op['description'],
                    'commune_id' => $communeId,
                    'website' => $op['website'],
                    'status' => 'approved',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
            $orgId = DB::table('organizations')->where('slug', $op['slug'])->value('id');

            foreach ($op['activities'] as $actSlug) {
                $catId = DB::table('business_categories')->where('slug', $actSlug)->value('id');
                DB::table('organization_categories')->updateOrInsert([
                    'organization_id' => $orgId,
                    'business_category_id' => $catId,
                ]);
            }

            $expActivityId = DB::table('business_categories')->where('slug', $op['experience']['activity'])->value('id');
            DB::table('experiences')->updateOrInsert(
                ['organization_id' => $orgId, 'slug' => Str::slug($op['experience']['name'])],
                [
                    'destination_id' => $destinationId,
                    'activity_type_id' => $expActivityId,
                    'name' => $op['experience']['name'],
                    'difficulty' => $op['experience']['difficulty'],
                    'status' => 'published',
                    'is_featured' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            echo "Operador cargado: {$op['name']}\n";
        }

        echo "LISTO\n";
    }
}