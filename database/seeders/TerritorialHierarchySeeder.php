<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TerritorialHierarchySeeder extends Seeder
{
    public function run(): void
    {
        DB::table('regions')->updateOrInsert(
            ['code' => '13'],
            [
                'name' => 'Región Metropolitana de Santiago',
                'slug' => 'metropolitana-de-santiago',
                'updated_at' => now(),
            ]
        );
        $regionId = DB::table('regions')->where('code', '13')->value('id');

        DB::table('provinces')->updateOrInsert(
            ['region_id' => $regionId, 'code' => '132'],
            ['name' => 'Cordillera', 'slug' => 'cordillera', 'updated_at' => now()]
        );
        $provinceId = DB::table('provinces')->where('code', '132')->value('id');

        $communes = [
            ['code' => '13201', 'name' => 'Puente Alto', 'slug' => 'puente-alto'],
            ['code' => '13202', 'name' => 'Pirque', 'slug' => 'pirque'],
            ['code' => '13203', 'name' => 'San José de Maipo', 'slug' => 'san-jose-de-maipo'],
        ];

        foreach ($communes as $commune) {
            DB::table('communes')->updateOrInsert(
                ['code' => $commune['code']],
                [
                    'province_id' => $provinceId,
                    'name' => $commune['name'],
                    'slug' => $commune['slug'],
                    'updated_at' => now(),
                ]
            );
        }

        $sanJoseId = DB::table('communes')->where('code', '13203')->value('id');
        DB::table('destinations')
            ->where('slug', 'cajon-del-maipo')
            ->whereNull('commune_id')
            ->update(['commune_id' => $sanJoseId]);
    }
}