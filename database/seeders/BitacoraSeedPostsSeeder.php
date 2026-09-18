<?php

namespace Database\Seeders;

use App\Models\BlogPost;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Sprint Bitácora GO 1.0 — carga los 12 títulos editoriales del plan como
 * registros `draft`, sin cuerpo ni datos inventados (título + categoría
 * únicamente), para que el admin y la portada tengan estructura real que
 * completar en vez de partir de cero. No se marca ninguno como published:
 * eso lo decide el equipo editorial desde /admin/bitacora cuando cada
 * historia tenga contenido real.
 *
 * Ejecutar con: php artisan db:seed --class=BitacoraSeedPostsSeeder
 */
class BitacoraSeedPostsSeeder extends Seeder
{
    public function run(): void
    {
        $posts = [
            ['title' => 'Una ruta, una historia: Cerro El Carbón', 'category' => 'rutas'],
            ['title' => 'Cómo preparar una salida de trekking', 'category' => 'educacion'],
            ['title' => 'Caminar también es aprender', 'category' => 'educacion'],
            ['title' => 'Personas que mueven el territorio', 'category' => 'personas'],
            ['title' => 'Vivir del territorio', 'category' => 'personas'],
            ['title' => 'Nahuelbuta: caminar entre araucarias', 'category' => 'rutas'],
            ['title' => 'Un territorio, muchas historias', 'category' => 'territorio'],
            ['title' => 'Pichilemu: océano, naturaleza y comunidad', 'category' => 'territorio'],
            ['title' => 'No Dejar Rastro: salir sin dejar huella', 'category' => 'educacion'],
            ['title' => 'Qué llevar realmente a una salida outdoor', 'category' => 'educacion'],
            ['title' => '¿Por qué proteger un bosque nativo?', 'category' => 'conservacion'],
            ['title' => 'Proyectos que transforman', 'category' => 'conservacion'],
        ];

        foreach ($posts as $data) {
            $slug = Str::slug($data['title']);

            BlogPost::firstOrCreate(
                ['slug' => $slug],
                [
                    'title' => $data['title'],
                    'category' => $data['category'],
                    'content' => '',
                    'status' => 'draft',
                ]
            );
        }
    }
}
