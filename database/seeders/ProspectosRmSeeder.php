<?php

namespace Database\Seeders;

use App\Models\Prospect;
use Illuminate\Database\Seeder;

/**
 * GO_CHILE_PROSPECTOS_RM.md/.csv — primeros 12 prospectos reales de la
 * Región Metropolitana (Cajón del Maipo / San José de Maipo), cargados
 * para arrancar el programa GO 20 Fundadores (§14-15 del modelo comercial).
 *
 * Idempotente por business_name (updateOrCreate) — correrlo de nuevo
 * actualiza estos 12 registros en vez de duplicarlos.
 *
 * Los campos marcados "Por verificar" o "— confirmar" en la fuente no se
 * cargan como texto placeholder en whatsapp/email/instagram (rompería el
 * uso real de esos campos, p.ej. wa.me/{whatsapp}) — quedan null y la
 * advertencia se suma a `notes`.
 */
class ProspectosRmSeeder extends Seeder
{
    public function run(): void
    {
        $prospects = [
            [
                'business_name' => 'Ruta Vertical Turismo Aventura Ltda.',
                'contact_name' => null,
                'territory' => 'Cajón del Maipo / San José de Maipo',
                'category' => 'Rafting y turismo aventura',
                'whatsapp' => '+56 9 9435 3143',
                'email' => 'info@rutavertical.cl',
                'instagram' => '@raftingrutavertical',
                'website' => 'https://www.rutavertical.cl/',
                'observed_problem' => 'Tiene presencia digital; oportunidad en ficha territorial, agenda y conexión con otros operadores',
                'source' => 'Búsqueda web',
                'notes' => 'Contacto oficial publicado en su sitio web. Persona de contacto por identificar.',
            ],
            [
                'business_name' => 'Isoterma',
                'contact_name' => null,
                'territory' => 'San José de Maipo',
                'category' => 'Turismo aventura, rafting, canyoning, paintball y picnic',
                'whatsapp' => '+56 9 9789 7035',
                'email' => 'info@isoterma.cl / isotermaturismo@gmail.com',
                'instagram' => '@isotermaturismo',
                'website' => 'https://www.isoterma.cl/',
                'observed_problem' => 'Tiene web y redes; oportunidad en agenda y presencia territorial integrada',
                'source' => 'Catálogo de servicios turísticos RM',
                'notes' => 'Reconfirmar datos antes de contactar. Persona de contacto por identificar.',
            ],
            [
                'business_name' => 'Maipo Adventure',
                'contact_name' => 'Pamela Zagal — confirmar',
                'territory' => 'Cajón del Maipo / San José de Maipo',
                'category' => 'Domos, cabañas, trekking, cabalgatas, escalada y turismo outdoor',
                'whatsapp' => '+56 9 7797 3724',
                'email' => 'contacto@maipoadventure.cl',
                'instagram' => '@maipo_adventure',
                'website' => 'https://www.maipoadventure.cl/',
                'observed_problem' => 'Tiene presencia digital; oportunidad en agenda, experiencias y posicionamiento territorial',
                'source' => 'Catálogo Metropolitana / ProChile',
                'notes' => 'Confirmar identidad del contacto.',
            ],
            [
                'business_name' => 'Andes Soul',
                'contact_name' => null,
                'territory' => 'Cajón del Maipo / Melocotón',
                'category' => 'Rafting, paintball, picnic y turismo aventura',
                'whatsapp' => null,
                'email' => null,
                'instagram' => '@Andes_Soul',
                'website' => 'https://www.andes-soul.com/',
                'observed_problem' => 'Tiene web y redes; oportunidad en agenda, contenidos y conexión territorial',
                'source' => 'Búsqueda web',
                'notes' => 'Revisar datos antes de contactar. Teléfono/WhatsApp y email por verificar. Persona de contacto por identificar.',
            ],
            [
                'business_name' => 'Natexplora',
                'contact_name' => null,
                'territory' => 'San José de Maipo',
                'category' => 'Rafting y canyoning',
                'whatsapp' => '+56 9 8552 5173',
                'email' => 'info@natexplora.cl',
                'instagram' => null,
                'website' => 'https://natexplora.cl/',
                'observed_problem' => 'Tiene web y reservas; oportunidad en ficha GO, agenda y campañas territoriales',
                'source' => 'Catálogo ProChile y sitio oficial',
                'notes' => 'Reconfirmar teléfono y contacto. Instagram por verificar.',
            ],
            [
                'business_name' => 'La Cumbre Natural Camp',
                'contact_name' => 'Javier — confirmar apellido',
                'territory' => 'San José de Maipo',
                'category' => 'Rapel, rafting, escalada y turismo aventura',
                'whatsapp' => '+56 9 6607 1188',
                'email' => 'lacumbrenaturalcamp@gmail.com',
                'instagram' => '@lacumbrenaturalcamp',
                'website' => 'https://lacumbrenaturalcamp.my.canva.site/',
                'observed_problem' => 'Presencia digital básica; reservas principalmente vía Instagram; falta ficha estructurada con experiencias y agenda',
                'source' => 'Sitio web encontrado en búsqueda',
                'notes' => 'Confirmar identidad del contacto.',
            ],
            [
                'business_name' => 'G Bike Tours',
                'contact_name' => null,
                'territory' => 'Cajón del Maipo / Pirque',
                'category' => 'Mountain bike, senderismo, trekking y turismo sustentable',
                'whatsapp' => null,
                'email' => null,
                'instagram' => null,
                'website' => 'https://www.gbiketours.cl/',
                'observed_problem' => 'Tiene web; oportunidad en agenda, conservación, rutas y alianzas territoriales',
                'source' => 'Sitio oficial',
                'notes' => 'Reconfirmar datos. Teléfono, email, Instagram y contacto por verificar/identificar.',
            ],
            [
                'business_name' => 'Origen del Maipo',
                'contact_name' => null,
                'territory' => 'Cajón del Maipo',
                'category' => 'Alojamiento, trekking, cabalgatas y bienestar',
                'whatsapp' => null,
                'email' => null,
                'instagram' => null,
                'website' => 'https://origendelmaipo.cl/',
                'observed_problem' => 'Tiene web; oportunidad en integración con operadores vecinos y oferta territorial conjunta',
                'source' => 'Sitio oficial',
                'notes' => 'Reconfirmar datos. Teléfono, email, Instagram y contacto por verificar/identificar.',
            ],
            [
                'business_name' => 'Parque Pailalén',
                'contact_name' => null,
                'territory' => 'Cajón del Maipo',
                'category' => 'Turismo de naturaleza, alojamiento y experiencias',
                'whatsapp' => '+56 9 4272 5922',
                'email' => null,
                'instagram' => null,
                'website' => 'https://pailalen.cl/',
                'observed_problem' => 'Tiene web; oportunidad en agenda, rutas cercanas y contenido territorial',
                'source' => 'Catálogo ProChile',
                'notes' => 'Reconfirmar contacto. Email e Instagram por verificar.',
            ],
            [
                'business_name' => 'Rancho El Añil',
                'contact_name' => null,
                'territory' => 'Cajón del Maipo',
                'category' => 'Turismo rural, alojamiento y experiencias',
                'whatsapp' => '+56 9 8827 0371',
                'email' => 'reservas@ranchoelanil.cl',
                'instagram' => null,
                'website' => 'https://ranchoelanil.cl/',
                'observed_problem' => 'Tiene web; oportunidad en perfil GO, agenda y conexión con experiencias de montaña',
                'source' => 'Catálogo ProChile',
                'notes' => 'Reconfirmar datos. Instagram por verificar.',
            ],
            [
                'business_name' => 'Hostería Millahue',
                'contact_name' => null,
                'territory' => 'San José de Maipo',
                'category' => 'Hostería / alojamiento',
                'whatsapp' => '+56 2 2861 2020',
                'email' => null,
                'instagram' => null,
                'website' => 'https://www.hosteriamillahue.com/',
                'observed_problem' => 'Tiene web; oportunidad en visibilidad dentro del ecosistema Cajón del Maipo',
                'source' => 'Catálogo ProChile',
                'notes' => 'Reconfirmar datos. Email e Instagram por verificar. Teléfono es fijo, no WhatsApp real.',
            ],
            [
                'business_name' => 'Andes del Maipo',
                'contact_name' => 'Claudio — confirmar rol',
                'territory' => 'Cajón del Maipo / Pirque',
                'category' => 'Portal turístico, paquetes, alojamiento y experiencias',
                'whatsapp' => '+56 9 7943 5625',
                'email' => null,
                'instagram' => null,
                'website' => 'https://www.andesdelmaipo.com/',
                'observed_problem' => 'Iniciativa territorial; puede ser aliado además de prospecto comercial',
                'source' => 'Sitio oficial',
                'notes' => 'Evaluar como posible aliado estratégico territorial. Email e Instagram por verificar.',
            ],
        ];

        foreach ($prospects as $data) {
            Prospect::updateOrCreate(
                ['business_name' => $data['business_name']],
                $data + ['status' => 'prospecto']
            );
        }
    }
}
