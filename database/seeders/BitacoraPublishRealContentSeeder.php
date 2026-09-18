<?php

namespace Database\Seeders;

use App\Models\BlogPost;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Sprint Bitácora GO 1.0 — publica 6 de los 12 borradores cargados por
 * BitacoraSeedPostsSeeder con contenido real (revisado y aprobado por
 * Nahuel). Actualiza por slug (no crea registros nuevos): si alguno de
 * estos 6 ya fue editado a mano en /admin/bitacora, correr esto lo
 * pisaría — no ejecutar dos veces sin revisar antes.
 *
 * published_at se escalona a propósito (no todos el mismo día) y evita
 * que dos artículos de la misma categoría (educación) queden pegados
 * en el tiempo, según el criterio que definimos con Nahuel:
 *  - "Un territorio, muchas historias" queda como la más antigua (pieza
 *    ancla/fundacional de la Bitácora, no la más destacada).
 *  - "Qué llevar realmente a una salida outdoor" queda como la más
 *    reciente (la que se ve en "Historias destacadas" en /bitacora).
 *  - "Caminar también es aprender" no queda pegada a "Cómo preparar
 *    una salida de trekking" (misma categoría/tono) — se intercala
 *    con la de conservación entre medio.
 *
 * Los otros 6 títulos (Cerro El Carbón, Nahuelbuta, Pichilemu, Personas
 * que mueven el territorio, Vivir del territorio, Proyectos que
 * transforman) siguen en draft: necesitan datos reales de ruta/persona/
 * proyecto que todavía no tenemos.
 *
 * Ejecutar con: php artisan db:seed --class=BitacoraPublishRealContentSeeder --force
 */
class BitacoraPublishRealContentSeeder extends Seeder
{
    public function run(): void
    {
        $posts = [
            [
                'title' => 'Cómo preparar una salida de trekking',
                'excerpt' => 'Lo que separa una salida segura de una improvisada no es el equipo caro, es la preparación.',
                'content' => <<<'TXT'
Antes de salir, lo primero es elegir una ruta acorde a tu nivel real, no al que te gustaría tener. Revisá el pronóstico del tiempo el mismo día de la salida, no la noche anterior: la montaña cambia rápido. Avisale a alguien —familia, pareja, un grupo— por dónde vas y a qué hora pensás volver.

Salí temprano. La mayoría de los problemas en la montaña no pasan por falta de fuerza, sino por quedarse sin luz de día. Calculá tu horario de retorno con margen y respetalo aunque la ruta se vea tentadora más adelante.

Llevá agua y comida de sobra, no la justa. Un botiquín básico, protección solar y una capa extra de abrigo no ocupan espacio y pueden cambiar el desenlace de un imprevisto. Y si hay algo que no conocés de la ruta, preguntale a quien sí la conoce antes de salir.
TXT,
                'published_at' => '2026-08-21 09:00:00',
            ],
            [
                'title' => 'Caminar también es aprender',
                'excerpt' => 'Cada ruta enseña algo que no está en ninguna guía.',
                'content' => <<<'TXT'
Caminar en la montaña obliga a prestar atención: al terreno, al cuerpo, al clima que cambia sin avisar. Esa atención es una forma de aprendizaje que no se enseña en un aula — se entrena kilómetro a kilómetro.

También enseña paciencia. El ritmo de la montaña no negocia con el apuro, y quien intenta forzarlo generalmente termina pagándolo con una caída, un esguince o simplemente agotado antes de llegar. Ir más lento, muchas veces, es ir mejor.

Y enseña a mirar el territorio de otra forma: un valle, un bosque o un río dejan de ser paisaje de fondo para convertirse en algo que se entiende, se respeta y —con el tiempo— se cuida.
TXT,
                'published_at' => '2026-09-01 09:00:00',
            ],
            [
                'title' => 'Un territorio, muchas historias',
                'excerpt' => 'Detrás de cada lugar hay personas, rutas y proyectos que lo hacen lo que es.',
                'content' => <<<'TXT'
Un territorio no es solo un punto en el mapa. Es la persona que abrió una ruta hace años, la organización que hoy protege un bosque, la comunidad que vive de él y lo cuida al mismo tiempo. Todo eso ocurre en el mismo lugar, muchas veces sin que se vea desde afuera.

La Bitácora GO nace para contar esas historias que normalmente quedan detrás del paisaje: quiénes son las personas que conocen cada ruta, qué proyectos están trabajando por conservarla, qué experiencias existen para vivirla de verdad.

Con el tiempo, cada historia que publiquemos acá va a poder conectarse con el resto del ecosistema GO Chile: con el territorio exacto del que habla, con las rutas que menciona, con las organizaciones y proyectos involucrados. De a poco, no de golpe — pero esa es la idea de fondo.
TXT,
                'published_at' => '2026-08-15 09:00:00',
            ],
            [
                'title' => 'No Dejar Rastro: salir sin dejar huella',
                'excerpt' => 'Siete principios simples para que la naturaleza que visitamos siga ahí para el próximo.',
                'content' => <<<'TXT'
No Dejar Rastro (Leave No Trace) es un conjunto de principios de ética outdoor reconocido internacionalmente, pensado para minimizar el impacto humano en espacios naturales. Son siete ideas simples, no una lista de prohibiciones:

Planificar y prepararse con anticipación, para no improvisar decisiones que dañen el entorno. Movernos y acampar sobre superficies durables, evitando pisotear vegetación frágil. Eliminar los desechos correctamente — lo que se lleva a la montaña, se vuelve a bajar, sin excepción. Dejar lo que se encuentra: piedras, plantas, restos culturales o históricos, tal como están. Minimizar el impacto de las fogatas, o directamente evitarlas donde el ecosistema es sensible. Respetar la fauna silvestre, observando desde lejos y sin alimentar animales. Y ser considerado con el resto de las personas que también están disfrutando ese mismo lugar.

Ninguno de estos principios pide dejar de salir a la montaña. Piden salir de una forma que no le cueste el lugar a quien venga después.
TXT,
                'published_at' => '2026-09-04 09:00:00',
            ],
            [
                'title' => 'Qué llevar realmente a una salida outdoor',
                'excerpt' => 'Menos marketing de tienda outdoor y más sentido común.',
                'content' => <<<'TXT'
La ropa en capas sigue siendo la base: una capa que abrigue, una que corte el viento y, si hay chance de lluvia, una impermeable. El calzado importa más que casi cualquier otro ítem — cómodo, ya probado en caminatas previas, nunca estrenado el mismo día de la salida.

Agua suficiente para todo el trayecto (y un poco más), protección solar, y un botiquín básico son innegociables, sin importar lo corta que parezca la ruta. Un mapa o una app de rutas descargada offline evita depender de la señal, que en montaña casi nunca está.

Y dos cosas que se olvidan seguido: una batería portátil para el teléfono, y una bolsa para bajar la propia basura — incluida la orgánica que se demora en descomponerse, como cáscaras de fruta.
TXT,
                'published_at' => '2026-09-06 09:00:00',
            ],
            [
                'title' => '¿Por qué proteger un bosque nativo?',
                'excerpt' => 'Lo que se pierde cuando se pierde un bosque nativo no se reemplaza plantando árboles de otra especie.',
                'content' => <<<'TXT'
Un bosque nativo no es solo un conjunto de árboles: es un ecosistema completo que tardó siglos en formarse, con una biodiversidad de flora y fauna adaptada específicamente a esas especies y a ese suelo. Reemplazarlo por una plantación de una sola especie —por más rápido que crezca— no reconstruye ese equilibrio, aunque el paisaje se vea "verde" desde lejos.

Los bosques nativos cumplen funciones que van más allá de lo visible: regulan el ciclo del agua, protegen el suelo de la erosión, y sostienen a comunidades que dependen de ellos para vivir. En Chile, además, muchos de estos bosques cargan un valor cultural y ancestral que no tiene reemplazo posible.

Protegerlos no es solo una decisión ambiental — es una decisión sobre qué territorio le dejamos a quien venga después.
TXT,
                'published_at' => '2026-08-27 09:00:00',
            ],
        ];

        foreach ($posts as $data) {
            $slug = Str::slug($data['title']);

            $updated = BlogPost::where('slug', $slug)->update([
                'excerpt' => $data['excerpt'],
                'content' => trim($data['content']),
                'status' => 'published',
                'published_at' => $data['published_at'],
            ]);

            if (! $updated) {
                $this->command?->warn("No se encontró un BlogPost con slug '{$slug}' — ¿corriste antes BitacoraSeedPostsSeeder?");
            }
        }
    }
}
