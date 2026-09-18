<?php

namespace Database\Seeders;

use App\Models\BlogPost;
use App\Models\Destination;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Sprint editorial "Rutas Reales" — publica 4 fichas de ruta con datos
 * verificados por fetch directo o citados con fuente (Parquemet,
 * Andeshandbook, Wikiloc, AllTrails, Asociación Parque Cordillera).
 *
 * "Una ruta, una historia: Cerro El Carbón" reutiliza el título exacto del
 * plan original de 12 (BitacoraSeedPostsSeeder) para caer en el mismo slug
 * si ese draft ya existe. Los otros 3 títulos son nuevos, fuera de ese plan.
 *
 * Decisiones confirmadas por Nahuel (2026-09-06):
 *  - Sin migración a `routes`: los datos que la tabla no soporta (altitud,
 *    riesgos, cómo llegar, etc.) viven en el texto del artículo, no en
 *    columnas nuevas. Por eso ningún post acá setea related_route_id.
 *  - Cuevas del Manzano: 3 fuentes no convergen (8km / ~5km / ~11km) — se
 *    publica el rango explícito citando cada fuente, sin elegir una.
 *  - Cerro Manquehue: se publica solo el acceso Los Trapenses-La Dehesa.
 *    Agua del Palo queda excluido — Andeshandbook registra ese acceso como
 *    cerrado con una reja. No hay fuente confiable para el motivo del cierre
 *    (fecha, fallo judicial, etc.), así que no se menciona en el artículo.
 *
 * related_destination_id solo se completa para Cuevas del Manzano (cae
 * dentro de Cajón del Maipo, destino ya existente). Aguas de Ramón, Cerro
 * El Carbón y Manquehue no corresponden a ningún destino ya cargado —no se
 * inventa uno nuevo para esto.
 *
 * Ejecutar con: php artisan db:seed --class=BitacoraPublishRutasRealesSeeder
 */
class BitacoraPublishRutasRealesSeeder extends Seeder
{
    public function run(): void
    {
        $authorId = User::where('email', env('ADMIN_SEED_EMAIL', 'go@0km.app'))->value('id');
        $cajonDelMaipoId = Destination::where('slug', 'cajon-del-maipo')->value('id');

        $posts = [
            [
                'title' => 'Una ruta, una historia: Cerro El Carbón',
                'excerpt' => 'Un clásico del Parque Metropolitano con dos accesos distintos y un horario que no perdona.',
                'related_destination_id' => null,
                'content' => <<<'TXT'
El Cerro El Carbón, parte del cordón del Manquehue dentro del Parque Metropolitano de Santiago, es uno de los senderos más visitados de la ciudad: cerca de 20.000 personas al año, según registros del propio parque. Su atractivo es simple — cuatro miradores en el trayecto (Las Pircas, El Litre, Guayacán y La Montaña) y una vista de Santiago que no exige experiencia previa.

Hay dos formas de subir. Desde Bosque Santiago el recorrido es de aproximadamente 8 km ida y vuelta, con un desnivel positivo de 683 m y una duración estimada de 4 a 4,5 horas. Desde la rotonda de La Pirámide, la distancia baja levemente a 7,9 km, con un desnivel similar (~700 m) pero un tiempo algo menor: 3,5 a 4 horas. Ambos accesos están señalizados y se puede llegar en transporte público (Metro Zapadores, línea 2, más buses Red).

El dato que más conviene anotar antes de salir: el acceso se cierra a las 15:00 hrs desde ambas entradas. No hay excepción por estar cerca de la cumbre. La entrada es gratuita y sin inscripción previa, pero no se permite el ingreso de mascotas, parlantes, bicicletas ni vehículos motorizados, y no está permitido hacer fogatas.

Lo único que ninguna fuente consultada especifica con claridad es el estacionamiento disponible en cada acceso — vale la pena confirmarlo el mismo día, especialmente si vas en auto y el plan es llegar temprano.
TXT,
                'published_at' => '2026-08-18 09:00:00',
            ],
            [
                'title' => 'Parque Natural Aguas de Ramón: cuatro senderos, un solo acceso',
                'excerpt' => 'Del paseo familiar de una hora a la subida técnica de un día completo — todo en el mismo parque.',
                'related_destination_id' => null,
                'content' => <<<'TXT'
El Parque Natural Aguas de Ramón, en la precordillera de Peñalolén, reúne cuatro senderos oficiales de distinta exigencia bajo un mismo acceso: Metro Plaza Egaña + microbús línea D15 hasta el paradero Álvaro Casanova con Onofre Jarpa. La entrada cuesta $3.450 para adulto general y $2.300 para adulto mayor y niños de 2 a 14 años.

Canto de Agua es la puerta de entrada más simple: 1 km aproximado, 30 minutos ida y vuelta, 30 m de desnivel hasta los 862 msnm. Ingreso hasta las 15:00 hrs. No se permite acampar, bañarse, fumar, hacer fogatas ni usar drones.

La Ruta Paleontológica sube un poco más: 3,7 km en circuito, 2 horas, 200 m de desnivel hasta los 1.030 msnm, con ingreso hasta las 13:00 hrs.

Los Peumos ya exige una jornada media: 6,5 km en circuito, 4 horas, 270 m de desnivel hasta los 1.100 msnm, mismo horario límite de ingreso (13:00 hrs).

Y Salto de Apoquindo es la exigente: 17,5 km ida y regreso, 7 horas, 814 m de desnivel hasta los 1.646 msnm — con una cascada como destino. Es la única con restricción de edad (mayores de 15 años) y con horarios escalonados: última entrada a las 09:00, corte en el Cruce del Estero a las 11:00, última bajada a las 13:00. Se exige calzado adecuado y no se permite acampar, bañarse bajo la cascada, fumar ni hacer fogatas.

Los cuatro senderos comparten algo más que el acceso: todos cierran temprano. Planificar la hora de entrada según el sendero elegido no es un detalle menor, es la diferencia entre completar la ruta y que te corten el paso a mitad de camino.
TXT,
                'published_at' => '2026-08-24 09:00:00',
            ],
            [
                'title' => 'Cuevas del Manzano: una ruta, tres distancias distintas',
                'excerpt' => 'Cuando las fuentes no coinciden, lo honesto es decirlo — no elegir la que suena mejor.',
                'related_destination_id' => $cajonDelMaipoId,
                'content' => <<<'TXT'
Las Cuevas del Manzano, formadas por erosión eólica sobre restos de una antigua erupción volcánica, son uno de los atractivos geológicos más conocidos del Cajón del Maipo, cerca de San José de Maipo. El acceso se hace por el Camino al Volcán hasta la localidad de El Manzano, con entrada por un sendero privado (alrededor de $2.000 por vehículo estacionado) junto a un silo, unos 2 km después del puente sobre el estero.

Acá viene lo que hay que decir con claridad: la distancia de esta ruta no está resuelta. Andeshandbook la reporta en 8 km (solo ida, 3 horas) con 850 m de desnivel, clasificándola como "moderada". Wikiloc, en cambio, la describe más corta —alrededor de 5 km— con 766 m de desnivel y una dificultad percibida como "difícil". Y agregadores como AllTrails suman una tercera cifra, cercana a los 11 km con más de 1.000 m de desnivel, posiblemente contabilizando un trazado o combinación de tramos distinta. Ninguna de las tres es claramente "la correcta": probablemente reflejan variantes reales del trazado, no errores de medición.

Lo que sí es consistente entre las fuentes: el sendero está poco marcado y de forma intermitente, no hay agua disponible en el trayecto, y existen sectores con formaciones rocosas sueltas donde es fácil resbalar o tomar un atajo que termina desorientando. Se recomienda zapatos de excursionismo, bastones opcionales y protección solar, evitar la ruta en pleno verano por el calor, y —si nevó recientemente— aprovechar que las vistas mejoran notablemente.

Si tu plan es esta ruta, andá con el dato claro: puede ser una caminata de medio día o una de jornada completa según el trazado que termines siguiendo. Preguntar en el lugar antes de salir no es opcional acá.
TXT,
                'published_at' => '2026-08-30 09:00:00',
            ],
            [
                'title' => 'Cerro Manquehue por Los Trapenses-La Dehesa',
                'excerpt' => 'La ruta "fácil" al Manquehue — y hoy, la única con acceso legal confirmado.',
                'related_destination_id' => null,
                'content' => <<<'TXT'
El Cerro Manquehue tiene más de un camino posible hasta la cumbre, pero no todos están disponibles hoy. El acceso histórico por Agua del Palo, en Vitacura, está cerrado actualmente con una reja, según registra Andeshandbook — más allá de lo que diga cualquier ficha técnica antigua. Por eso esta guía cubre la ruta que sí está abierta: Los Trapenses-La Dehesa.

El punto de partida es el estacionamiento al final de la Avenida El Golf de Manquehue, en La Dehesa, Lo Barnechea. Desde ahí hay dos variantes: la ruta normal, de aproximadamente 3 km solo ida, con un desnivel de 635 m y un tiempo estimado de 1½ a 2 horas de subida y 1 a 1½ horas de bajada; y una ruta directa, más corta (~2 km) pero sin sendero marcado, pensada para quien ya conoce el cerro.

Andeshandbook clasifica la ruta normal como "fácil" (2,3/7 en exigencia física, 1,8/7 en técnica) — el motivo por el que suele recomendarse como primera cumbre para quien recién empieza en el senderismo de montaña en Santiago. El sendero está bien delineado en toda su extensión (la variante directa, no). No hay reportes de cierre de acceso, y es recomendable todo el año, aunque mejora especialmente en primavera.

Lo que sí conviene cuidar: los sectores erosionados del camino, sobre todo al bajar, cuando el cansancio hace que se preste menos atención al paso. Se recomienda calzado de excursión y, en la variante directa, considerar guantes y casco. No hay información confirmada sobre transporte público hasta el punto de partida — a diferencia del Cerro El Carbón, acá conviene planear el traslado en auto.
TXT,
                'published_at' => '2026-09-05 09:00:00',
            ],
        ];

        foreach ($posts as $data) {
            $slug = Str::slug($data['title']);

            BlogPost::updateOrCreate(
                ['slug' => $slug],
                [
                    'title' => $data['title'],
                    'author_id' => $authorId,
                    'category' => 'rutas',
                    'excerpt' => $data['excerpt'],
                    'content' => trim($data['content']),
                    'related_destination_id' => $data['related_destination_id'],
                    'status' => 'published',
                    'published_at' => $data['published_at'],
                ]
            );
        }
    }
}
