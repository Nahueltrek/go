# Auditoría GO Chile

Fecha: 5 de septiembre de 2026. Alcance: todo `C:\gochile-code` tal como está en este momento (incluye el trabajo de Sprints 3a–7 ya aplicado: CRUD de Organización y Experiencia, landing pública rediseñada, navegación admin unificada, y la fusión inicial de Business dentro de Organization). Auditoría de solo lectura — no se modificó código de producto en este documento, solo se reemplaza este archivo.

---

## 1. Resumen ejecutivo

GO Chile corre sobre el esqueleto técnico de un proyecto anterior, **Ruta Cajón del Maipo 360** ("rm360"): un catastro turístico local de un solo destino (Business, Attraction, Route, Article, Event ligados a un `Destination` fijo). Ese esqueleto sigue **100% funcional y en producción**, pero tiene **0 registros reales** — nunca se cargó el catastro de negocios.

Encima de ese esqueleto, y sin tocarlo, se construyó una segunda capa — **GO Chile 2.0** — con entidades propias (`Organization`, `Experience`, `Project`, `BlogPost`, `CollaborationRequest`, más un `Event` extendido) que sí tiene datos reales cargados manualmente (9-10 colaboradores curados) y es donde vive todo el trabajo activo reciente. Esta capa ya cubre de forma sólida: home, buscador global, mapa nacional con clusters, agenda, bitácora, landing de operador y de experiencia, formulario público de postulación, y un panel admin con navegación unificada.

El resultado es un proyecto que **funciona y ya tiene bastante de la visión** (descubrir experiencias/operadores/proyectos, mapa nacional, buscador multi-tipo, CTA "Quiero ser parte"), pero que convive con:
- Una capa vieja completamente inactiva (0 filas) que todavía consume rutas, controladores, un layout admin, y una API REST completa.
- Inconsistencia visual fuerte: las páginas nuevas usan la paleta sky-900/sky-50 con marca "GO Chile"; varias páginas viejas (`/login`, `/dashboard`, `/atractivos/*`, `/rutas/*`, `/blog`, `/emprendimientos/*`) todavía dicen literalmente **"← Ruta 360"** y usan una paleta completamente distinta (ink/paper/river).
- Un bug de routing real: el dashboard admin (`/admin`) usa el controlador viejo (stats de Business/Review/Claim) en vez del nuevo (stats de Organization/Experience/Project/Event), así que **las tarjetas del dashboard admin están en blanco en producción ahora mismo**.
- Un problema de seguridad serio: la contraseña del usuario admin de producción está **hardcodeada en texto plano en un seeder versionado en git** (`CreateAdminSeeder.php`).
- Un bug de SEO/marca: el `<title>` de **todas** las páginas dice "— Ruta Cajón del Maipo 360" en vez de "— GO Chile", porque `VITE_APP_NAME` nunca se seteó en `.env`.

Ninguno de estos problemas es estructural — son todos arreglables sin reconstruir nada — pero conviene resolverlos antes de seguir escalando funcionalidades nuevas, porque afectan primera impresión (título de pestaña, dashboard vacío) y seguridad (password hardcodeada).

---

## 2. Stack actual

| Capa | Detalle |
|---|---|
| Backend | Laravel **^13.0**, PHP **^8.3** |
| Frontend | Inertia.js **^2.0** + Vue **^3.4** (`<script setup>`, sin TypeScript) |
| CSS | Tailwind **^4.0** (config vía `@theme` en `resources/css/app.css`, sin `tailwind.config.js`) |
| Mapas | MapLibre GL **^4.7.1** (no Mapbox/Google — sin costo de licencia) |
| Rutas del lado cliente | Ziggy **^2.4** (`route()` helper disponible en Vue) |
| Auth web | Sesión Laravel estándar (`auth` middleware, roles propios) |
| Auth API | Laravel Sanctum **^4.0** (tokens, no cookies) — solo cubre entidades rm360 |
| Base de datos | **MariaDB 11.8**, hosting compartido (no PostgreSQL/PostGIS — decisión documentada en `docs/GEO_MARIADB.md`) |
| Geoespacial | Tipos nativos MariaDB `POINT`/`POLYGON`/`LINESTRING` + `ST_Y`/`ST_X`/`ST_Distance_Sphere` vía el trait `HasGeoLocation` |
| Sesión/caché/colas | `file` / `file` / `sync` (confirmado en `.env` de producción — **no** son tablas de BD como describe `docs/FASE_1_README.md`; no hay Redis ni worker de colas corriendo) |
| Hosting | Hostinger compartido, LiteSpeed, sin Docker/root — deploy manual por `scp` (puerto 65002) a dos rutas + purga manual de LiteSpeed Cache Manager |
| Testing | PHPUnit **^12.0** — prácticamente sin cobertura (ver §9) |
| Composer package name | `0km/ruta-cajon-del-maipo-360` — nombre y `description`/`keywords` (`"postgis"`) siguen siendo los del proyecto original |

---

## 3. Arquitectura

Patrón general: **Laravel + Inertia** monolítico — no hay SPA separada ni build de frontend independiente del backend. Cada `Controller` renderiza un componente Vue vía `Inertia::render('Pagina', [...props])`; no hay Blade real más allá de `resources/views/app.blade.php` (el shell HTML raíz).

**Dos capas conviven en el mismo codebase:**

| | rm360 (legacy) | GO Chile 2.0 (activo) |
|---|---|---|
| Entidad núcleo | `Business` | `Organization` |
| Contenido | `Attraction`, `Route`, `Article`, `Event` (parcial) | `Experience`, `Project`, `BlogPost`, `CollaborationRequest`, `Event` (parcial) |
| Alcance geográfico | Un solo `Destination` fijo (Cajón del Maipo, sembrado por seeder) | Nacional — sin destino fijo |
| Paleta visual | `ink`/`paper`/`river`/`rock`/`glacier` (Tailwind `@theme`), tipografía Fraunces + IBM Plex | `sky-900`/`sky-50` (Tailwind stock), sin tipografía custom |
| Layout admin | `AdminLayout.vue` (ahora compartido, ver Sprint 6 abajo) | Header inline + `AdminLayout.vue` |
| Datos reales | **0 filas** | ~9-10 organizaciones curadas manualmente |
| API REST (`/api/v1/*`) | Sí, completa (Sanctum) | **No existe** — GO Chile 2.0 no tiene API, solo páginas Inertia |

**Capa admin (`app/Http/Controllers/Admin/*`)**: dos generaciones de páginas conviven bajo el mismo `AdminLayout.vue` desde el Sprint 6 (navegación lateral/tabs unificada, `Negocios` sin link visible pero ruta accesible). Las páginas rm360 (`Businesses`, `Claims`, `Reviews`, `Articles`) siguen con su paleta vieja dentro del nuevo shell — funcionan, pero se ven visualmente distintas al resto.

**Autorización**: Policies de Laravel estándar, auto-descubiertas (no hay registro manual en `AppServiceProvider`), una por entidad relevante (`OrganizationPolicy`, `ExperiencePolicy`, `ProjectPolicy`, `ArticlePolicy`, `BlogPostPolicy`, `BusinessPolicy`, `CollaborationRequestPolicy`). El grupo de rutas `/admin/*` además exige `role:admin,super_admin` a nivel de middleware, así que algunos controladores admin (`ReviewController`, `BusinessController`) no llaman `$this->authorize()` explícitamente — cubiertos por el middleware de grupo, no por policy individual (no es un agujero real, pero no es defensa en profundidad).

**Relaciones polimórficas** — mezcla de dos convenciones:
- `route_points`, `article_relations`, `media`, `galleries`, `favorites` usan un `morphMap()` corto (`business`, `attraction`, `activity`, `route`, `article`, `destination`) registrado en `AppServiceProvider`.
- `reviews.reviewable_type`/`favorites.favoritable_type` para `Organization`/`Experience` (agregado en Sprints 5 y 7) **no están en el `morphMap`**, así que Eloquent guarda el FQCN completo (`App\Models\Organization`) en vez de un alias corto. Funciona correctamente, pero es una inconsistencia de estilo entre lo viejo y lo nuevo — ver §9.

---

## 4. Base de datos

50 migraciones. Agrupadas por función (columnas/enums/FKs abreviados — el detalle línea por línea vive en cada migración):

**Territorial** (compartido por ambas capas): `regions` → `provinces` → `communes` (con `boundary` POLYGON nullable sin índice spatial) → `localities`; `destinations` (con `commune_id`, `boundary` nullable, `active_layers` JSON). Todas con `slug` (agregado en `2026_08_25_000001_alter_regions_provinces_communes_add_slug`). **Nota**: `Commune::$fillable` incluye `centroid_lat`/`centroid_lng`, columnas que **no existen** en la migración de `communes` — código muerto/inconsistente, ver §9.

**rm360 — núcleo**: `businesses` (spatial `location` NOT NULL, `sernatur_status`/`verification_status`/`claim_status`, soft deletes) + `business_services`/`business_locations`/`business_contacts`/`business_socials` (todas 1:N desde `businesses`, todas **0 filas**). `sernatur_records` + `sernatur_sync_logs` — módulo de importación SERNATUR con tablas listas pero **sin lógica activa** (documentado como tal en el propio código: `SernaturRecord.php` dice explícitamente "importador inactivo").

**rm360 — contenido**: `attractions` (spatial NOT NULL), `activities` (spatial nullable, liga opcional a `business_id`), `routes` (`path` LINESTRING nullable) + `route_points` (polimórfico: Business|Attraction|Activity), `article_categories`/`article_tags`/`articles`/`article_article_tag` (pivot)/`article_relations` (polimórfico).

**GO Chile 2.0 — núcleo**: `organizations` (agregado en Sprint 3a/4a/7: `cover_image`, `verification_status`, `claim_status`, `opening_hours`) + `organization_categories` (pivot con `business_categories`, reutilizada — ver nota abajo), `experiences` (spatial nullable, `cover_image`), `projects` (spatial nullable vía `ALTER... ADD location POINT NULL`, `category` enum de 4 valores fijos), `blog_posts` (sistema de contenido **paralelo** a `articles`, ver §9), `collaboration_requests` (formulario público de postulación, con método `approve()` en el modelo que crea la `Organization`).

**Compartido entre capas**:
- `business_categories` — 16 categorías activas, reutilizada tanto por `businesses.business_category_id` como por `organizations` (vía pivot) y `experiences.activity_type_id`. El nombre de tabla es heredado pero el contenido es legítimamente de ambas capas hoy — confirmado, no se debe renombrar sin más.
- `media`/`galleries` — polimórfico, usado por `Organization`... espera, en realidad `Media` se usa por `Experience`, `Project`, `Event`, `Attraction`, `Business` — **no** por `Organization` (que usa columnas planas `logo_url`/`cover_image` en vez de la tabla `media`).
- `reviews` — ya polimórfico (`reviewable_type`/`reviewable_id`, con `business_id` legacy conservado como fallback, migración `2026_08_25_000014_alter_reviews_polymorphic`). `Organization` tiene la relación `reviews()` desde el Sprint 7; nada en el producto todavía deja reseñar una `Organization` o `Experience` (solo `Business` tiene endpoint de creación de reviews, en `Api\V1\ReviewController` y el dashboard admin de moderación).
- `favorites` — polimórfico desde el inicio (`favoritable_type`/`favoritable_id`). `Organization` tiene `favoritedBy()` desde el Sprint 7, pero el `Api\V1\FavoriteController::toggle()` solo acepta `type: business|attraction|route` — **no reconoce `organization` ni `experience` todavía**, así que favoritear una organización no es posible desde ningún endpoint real hoy.
- `claims` — **no polimórfico**, FK directa `business_id` NOT NULL. No hay (ni se agregó en el Sprint 7, deliberadamente) un concepto de "reclamar" una `Organization`.
- `events` — tabla híbrida real: nace en rm360 (`business_id` nullable) y se extiende en el Sprint 4a-área (`2026_08_25_000016_alter_events_add_organization_fields`) con `organization_id`, `category`, `capacity`, `price`, `difficulty`, `status` — es la única tabla que sirve genuinamente a ambas capas a la vez.

**Auth**: `users` (soft deletes, sin campos de perfil más allá de `name`/`email`/`phone`), `roles`/`permissions`/`role_user`/`permission_role` (RBAC simple, propio, no Spatie), `personal_access_tokens` (Sanctum).

**`activities`** — tabla y modelo (`Activity.php`) casi idénticos en forma a `Experience` (destination, difficulty, duration_minutes, location, liga opcional a una entidad "quien la ofrece"), pero ligada a `business_id` (rm360, 0 filas) en vez de `organization_id`, y sin ninguna ruta pública ni página Vue encontrada que la sirva. Todo indica que quedó **superada por `Experience`** sin haberse retirado formalmente — candidata a confirmar-y-eliminar, no a mantener en paralelo.

**Construido pero sin ningún código de aplicación referenciándolo** (confirmado por grep — cero controladores, cero modelos de negocio los usan; algunos ni siquiera tienen modelo Eloquent):
- `leads` — sin modelo Eloquent.
- `plans` / `subscriptions` / `payments` — sin modelos, documentado en la propia migración como "arquitectura de monetización futura, no implementar antes de validar el MVP".
- `audit_logs` — sin modelo Eloquent.
- `sernatur_sync_logs` — sin modelo Eloquent.
- `verifications` — modelo existe (`Verification.php`), pero ningún controlador lo usa (Business tiene `verification_status` como enum plano, sin pasar por esta tabla).
- `notifications` (tabla estándar de Laravel) — `User` usa el trait `Notifiable`, pero no hay ni una sola clase `Notification` en el proyecto ni un `->notify()` en ningún lado.

**Estándar de Laravel**: `sessions`, `cache`, `cache_locks`, `jobs`, `failed_jobs`, `job_batches`, `password_reset_tokens` (sin ruta de "olvidé mi contraseña" wireada — tabla presente, feature ausente).

---

## 5. Funcionalidades actuales

| Funcionalidad | Estado |
|---|---|
| Home pública con hero, actividades, mapa CTA, experiencias destacadas, agenda, red de operadores, bitácora, CTA colaboradores | ✅ Funcionando (`HomeController` + `Public/Home.vue`, completo y pulido) |
| Buscador global multi-tipo (experiencias/operadores/proyectos/negocios/rutas/eventos) con filtros por región/comuna/actividad/dificultad/precio | ✅ Funcionando (`SearchController` + `Public/Explorar.vue`) |
| Mapa nacional con clustering y capas por tipo | ✅ Funcionando (`MapController::geojson` + `Public/Mapa.vue`, MapLibre) |
| Landing de Operador (portada, mapa, categorías, contacto, experiencias/proyectos asociados) | ✅ Funcionando (Sprint 4a/5, componentes compartidos) |
| Landing de Experiencia (portada, chips, stats, mapa con fallback a ubicación del operador) | ✅ Funcionando (Sprint 5) |
| CRUD admin de Organización (listar/aprobar/suspender/editar) | ✅ Funcionando (Sprint 3a/4a/7) |
| CRUD admin de Experiencia (crear/listar/editar/eliminar) | ✅ Funcionando (Sprint 4b) |
| Navegación admin unificada | ✅ Funcionando (Sprint 6) |
| Formulario público "Quiero ser parte" + aprobación admin (crea Organization) | ⚠️ Funciona, pero el mensaje de confirmación nunca se muestra (bug de flash, ver §9) |
| Agenda de eventos (listado + ficha) | ✅ Funcionando, visualmente completo |
| Bitácora (listado + post, con categorías) | ✅ Funcionando |
| Landing de Proyecto | ✅ Funcionando, más simple que Operador/Experiencia (no usa los componentes compartidos del Sprint 5 todavía) |
| Dashboard admin con stats | ❌ **Roto** — ruta wireada al controlador incorrecto, tarjetas en blanco (ver §9) |
| Panel "mis negocios" del dueño (Owner) | ⚠️ Funciona pero solo cubre `Business` (0 filas) — no existe un equivalente para dueños de `Organization` |
| Reclamo de ficha de negocio (Claim) | ✅ Funcionando (solo para `Business`) |
| Reseñas (crear/moderar) | ✅ Funcionando (solo para `Business`, vía API v1 — no hay UI web para dejar una reseña) |
| Favoritos | ⚠️ Funciona solo para `business`/`attraction`/`route` vía API — sin UI web en absoluto |
| Login admin | ✅ Funciona, pero visualmente "Ruta 360" (ver §10) |
| Blog viejo (`/blog`) vs Bitácora nueva (`/bitacora`) | ⚠️ Ambos activos y funcionando en paralelo — dos sistemas de contenido editorial sin unificar |
| Páginas de Atractivo/Ruta/Negocio (rm360) | ✅ Funcionan (0 datos que mostrar), visualmente "Ruta 360" |
| API REST v1 (Sanctum) | ✅ Funciona, pero **no cubre ninguna entidad de GO Chile 2.0** (Organization/Experience/Project/BlogPost ausentes de la API) |

---

## 6. Páginas actuales

**Públicas — GO Chile 2.0 (paleta sky, marca "GO Chile", completas):**
`/` (Home), `/explorar`, `/mapa`, `/operadores/{slug}`, `/experiencias/{slug}`, `/proyectos/{slug}`, `/agenda`, `/agenda/{slug}`, `/bitacora`, `/bitacora/{slug}`, `/colaboradores` (form).

**Públicas — rm360 legacy (paleta ink/paper, marca "Ruta 360", funcionan con 0 datos):**
`/emprendimientos/{slug}`, `/atractivos/{slug}`, `/rutas/{slug}`, `/blog`, `/blog/{slug}`.

**Auth/Owner (paleta ink/paper, marca "Ruta 360"):**
`/login`, `/dashboard` (mis negocios), `/dashboard/negocios/{slug}/editar`.

**Admin (paleta mixta bajo `AdminLayout` unificado):**
`/admin` (dashboard, **roto**), `/admin/organizaciones` (+ editar), `/admin/experiencias` (+ nueva/editar), `/admin/colaboradores`, `/admin/businesses` (sin link en nav), `/admin/claims`, `/admin/reviews`, `/admin/articles` (+ nuevo/editar).

Ninguna página pública tiene un layout compartido explícito (`resources/js/Layouts/` solo tiene `AdminLayout.vue`) — cada página pública repite su propio `<header>` inline. Es codebase pequeño, así que no es grave todavía, pero escalará mal si se agregan más páginas públicas.

---

## 7. Dashboard

Hay **dos dashboards admin completos** compitiendo por la misma ruta:

- `Admin\DashboardController` (invokable) — stats de `Business::active()`, `Review::pending`, `Claim::pending`. **Este es el que está wireado** en `routes/web.php:66` (`Route::get('/', AdminDashboardController::class)->name('dashboard')`).
- `Admin\GoChileDashboardController` — stats de `Organization`/`CollaborationRequest`/`Experience`/`Project`/`Event`/`BlogPost`, con las claves exactas (`colaboraciones_pendientes`, `organizaciones_pendientes`, etc.) que `Admin/Dashboard.vue` espera leer. **No está wireado en ninguna ruta** — código huérfano.

Resultado: `Admin/Dashboard.vue` lee `stats.colaboraciones_pendientes`, `stats.organizaciones_pendientes`, etc., pero el controlador real nunca las provee → todas las tarjetas muestran `undefined` en vez de un número. Fix es trivial (un import + una clase en `routes/web.php`), pero es la primera cosa que ve un admin al entrar al panel.

El dashboard del dueño de negocio (`Owner\DashboardController` → `Dashboard/Index.vue`) funciona correctamente pero solo lista `Business` del usuario — no existe today un dashboard equivalente para el dueño de una `Organization`.

---

## 8. Contenido existente

Heredado del `docs/AUDITORIA_GO_CHILE.md` anterior (Sprint 2, 28 de agosto de 2026) y confirmado vigente:
- Eliminadas 3 organizations demo (Natexplora, Isoterma Turismo, Ruta Vertical) y sus experiencias/categorías asociadas.
- 23 categorías nuevas creadas en `business_categories` (total histórico 29 antes de la consolidación del Sprint 7).
- 9 colaboradores reales cargados en `organizations` con `status=pending`: Green Sport, Ríos Libres Experience, Contentour, Cantauria, Zona Verde Pichilemu, RAWKO, Surkaterra, Proteas del Mar, Proyecto Rocas de Constitución.
- Kuri quedó pendiente de validar (no cargado).
- `collaboration_requests` id 2 (Paseos Náuticos Cochrane) quedó pendiente de revisión manual — no incorporado como organization en ese sprint (estado actual sin confirmar en esta auditoría — requiere chequeo directo en BD).

Desde entonces (Sprints 3a-7, este trabajo):
- A los 9-10 colaboradores se les asignaron URLs de portada de prueba (picsum.photos, placeholders explícitos, no fotos reales).
- `business_categories` se consolidó de 29 a un set más chico (Sprint 7: fusión de categorías redundantes en `conservacion`/`educacion-ambiental`/`sostenibilidad`/`cultura-territorio`/`turismo` — ejecutado por el usuario directamente en producción, fuera de este repo).
- `businesses` y sus 4 tablas satélite: confirmado **0 filas** siempre — nunca se cargó el catastro SERNATUR.

No hay forma de confirmar el estado exacto y actual de filas en producción desde este entorno local (sin acceso a la base de datos) — todo lo anterior es histórico/documental, no una consulta en vivo.

---

## 9. Problemas técnicos

1. **Dashboard admin roto** — ver §7. Severidad alta, fix trivial.
2. **`Public\HomeController` es código muerto** — existe, renderiza `Public/Home` con un shape de props completamente distinto (`destination`/`categories`/`businesses`/`attractions`/`routes`) al que realmente usa `Home.vue` hoy (`activities`/`featuredExperiences`/`upcomingEvents`/`latestPosts`/`networkOrganizations`, provisto por `App\Http\Controllers\HomeController`, sin namespace `Public\`). Si alguna vez se referencia por error, rompe la home entera. Candidato a eliminar.
3. **Mensajes flash de Inertia nunca llegan al frontend** — `HandleInertiaRequests::share()` solo comparte `auth.user`; nunca agrega `'flash' => [...]`. Pero `Organizaciones.vue`, `Experiencias/Index.vue`, `Colaboradores.vue` (admin) y `Public/Colaboradores.vue` (formulario público) todos leen `page.props.flash?.success` esperando ver un banner de confirmación. Las acciones (aprobar/suspender/crear/eliminar/postular) **funcionan igual** — solo la confirmación visual nunca aparece. Afecta un flujo público core (postular a "Quiero ser parte").
4. **`Commune::$fillable` referencia columnas inexistentes** (`centroid_lat`, `centroid_lng`) — la migración de `communes` nunca las creó. Inofensivo hoy (nada las asigna), pero es deuda de código que puede confundir a futuro.
5. **`morphMap()` inconsistente** — cubre entidades rm360 (`business`, `attraction`, `activity`, `route`, `article`, `destination`) pero no `Organization`/`Experience`, que quedan guardadas con el FQCN completo en `reviewable_type`/`favoritable_type`. Funciona, pero es dos convenciones a mantener.
6. **Sin `DatabaseSeeder.php`** — no hay un seeder maestro; cada seeder (`RolesAndPermissionsSeeder`, `CreateAdminSeeder`, `CajonDelMaipoDestinationSeeder`, `TerritorialHierarchySeeder`, `GoChileRealDataSeeder`) debe ejecutarse a mano con `--class=`, en el orden correcto, documentado solo en `docs/FASE_1_README.md`. Riesgo de reconstrucción de entorno.
7. **API v1 no cubre GO Chile 2.0** — `Organization`, `Experience`, `Project`, `BlogPost` no tienen ningún endpoint REST. Si se planea una app móvil o integración externa, hay que construir esa capa desde cero.
8. **`opening_hours` como textarea de JSON crudo** (`Admin/OrganizacionesEdit.vue`, Sprint 7) — funcional pero fricción real para un admin no técnico; es una solución deliberadamente mínima, no un editor estructurado.
9. **Sin cola real** — `QUEUE_CONNECTION=sync` en producción. Cualquier trabajo pesado futuro (envío de emails, procesamiento de imágenes) se ejecuta inline, bloqueando el request.
10. **`MAIL_MAILER=log`** — ningún correo sale realmente (todo queda en el log). No hay verificación de email ni recuperación de contraseña funcional en la práctica, aunque la tabla `password_reset_tokens` exista.
11. **Sin `.env.example`** — no hay plantilla de variables de entorno en el repo. Onboarding/disaster-recovery depende de reconstruir la lista de variables leyendo `config/*.php` a mano.
12. **`composer.json` con metadata vieja** — `name: 0km/ruta-cajon-del-maipo-360`, `keywords: [..., "postgis"]` (ya no se usa PostGIS). Cosmético, pero confunde a quien mira el repo por primera vez.
13. **Testing casi inexistente** — `tests/Feature/HealthCheckTest.php` solo verifica que `/up` responda 200. Cero cobertura de lógica de negocio, policies, o de los bugs listados acá.
14. **`Admin\ReviewController`/`Admin\ClaimController` acceden `$r->business->name`/`$c->business->name` sin guard** — funciona hoy porque solo hay reviews/claims de `Business`, pero si algún día una review llega a apuntar a `Organization`/`Experience` vía `reviewable_type` (la relación ya existe desde el Sprint 7), esa línea revienta con "Attempt to read property on null". No es un bug activo todavía, pero es una bomba de tiempo directamente ligada a una funcionalidad que este mismo proyecto ya habilitó a medias.
15. **Link roto en `Explorar.vue`** — los resultados de tipo "negocios" enlazan a `/negocios/{slug}`, pero la ruta pública real es `/emprendimientos/{slug}` (`BusinessShowController`). Con 0 `Business` en producción nadie lo nota todavía, pero es un 404 garantizado en cuanto haya un solo resultado de ese tipo.
16. **Lógica de mapa duplicada** — `Public/Mapa.vue` (mapa nacional con clusters) y `Components/MapView.vue` (mini-mapa embebido, usado en Operador/Experiencia/Atractivo/Ruta) inicializan MapLibre GL y arman markers de forma completamente independiente, sin ningún composable compartido. Cambios futuros a estilo de pines/popups hay que replicarlos dos veces.
17. **`Activity` model sigue FK'd a `Business`** (`business_id`, no `organization_id`) — huérfano por la deprecación del Sprint 7 igual que `Verification`/`Locality::businesses()`; ver también la nota sobre `activities` como posible duplicado de `Experience` en §4.
18. **`Gallery` (modelo + tabla `galleries`) sin ningún código que la use** — ninguna acción del código crea filas ahí; scaffolding sin conectar, similar a `leads`/`audit_logs`.
19. **Fuentes de Google (Fraunces + IBM Plex) cargan globalmente en `app.blade.php`** para todo el sitio, pero solo las páginas rm360-legacy las usan — las páginas GO Chile 2.0 (sky palette) usan la pila `sans` por defecto de Tailwind y nunca referencian esas fuentes. Peso de carga innecesario en cada página nueva.
20. **Posible relación obsoleta en `Api\V1\EventController`** (hallazgo sin confirmar) — parece llamar a `Event::business()` para el eager-load, mientras el `EventController` web usa `organization`/`destination`. No se leyó `Event.php` completo en esta pasada para confirmar si esa relación sigue existiendo tras la migración `alter_events_add_organization_fields` — verificar directamente antes de asumir que es un bug real.

---

## 10. Problemas UX

1. **Choque de marca/paleta al navegar** — un usuario que entra por `/` (GO Chile, sky, pulido) y toca `/atractivos/{slug}`, `/rutas/{slug}`, `/blog`, `/login`, o `/dashboard` aterriza en una página que dice literalmente **"← Ruta 360"** con una paleta totalmente distinta (ink/paper/river). Esto incluye el **login del propio admin** — la puerta de entrada al panel dice "Ruta 360 · Acceso administración".
2. **Sin confirmación visual tras acciones** — ver §9.3. Un admin que aprueba/suspende, o un visitante que postula a "Quiero ser parte", no recibe ninguna señal de que funcionó (más allá de que la lista se actualiza sola).
3. **Sin carga de imágenes real** — `cover_image`/`logo_url` en Organization/Experience son inputs de texto para pegar una URL externa (documentado y deliberado en los Sprints 4a/4b/5/7, no un descuido) — pero es una fricción real para el usuario final del admin, que probablemente no tiene sus fotos ya alojadas en una URL pública.
4. **Panel "mis negocios" solo para `Business`** — un dueño de `Organization` (el caso real y activo hoy) no tiene ningún panel de autogestión; todo pasa por el admin.
5. **Mezcla mobile-first / desktop-first en admin** — las páginas nuevas son mobile-first (`px-4`, sin `max-w`); las páginas rm360 dentro del mismo `AdminLayout` siguen centradas `max-w-5xl` — se nota el salto al navegar entre secciones del panel.

---

## 11. Problemas SEO

1. **`<title>` incorrecto en todo el sitio** — `resources/js/app.js` usa `import.meta.env.VITE_APP_NAME || 'Ruta Cajón del Maipo 360'` como fallback, y **`VITE_APP_NAME` no está seteado en `.env`** (solo `APP_NAME="GO Chile"`, que Vite no expone al frontend sin el prefijo `VITE_`). Resultado: cada página en producción tiene un `<title>` del tipo `"Operadores — Ruta Cajón del Maipo 360"`, no "GO Chile". Esto afecta resultados de búsqueda y previews al compartir enlaces — es de los hallazgos más impactantes de esta auditoría y el más barato de arreglar (una línea en `.env` + redeploy).
2. **Sin meta description / Open Graph / Twitter card / canonical / robots / favicon** — confirmado leyendo `resources/views/app.blade.php` completo: no tiene ninguno de esos tags, ni estáticos ni por página. Ninguna página Vue usa el componente `<Head>` de `@inertiajs/vue3` para inyectar metadatos propios tampoco. Los campos `meta_title`/`meta_description` que sí existen en `articles` no se usan en `BlogShowController` ni en `Public/BlogShow.vue` — están en la base de datos pero no llegan nunca al HTML. Esto afecta cómo se ve cada página al compartirse en redes/WhatsApp y cómo la indexa un buscador.
3. **Dos sistemas de contenido editorial en paralelo** (`articles` "/blog" vs `blog_posts` "/bitacora") — diluye autoridad de dominio y duplica esfuerzo editorial sin necesidad.

---

## 12. Problemas de seguridad

1. **CRÍTICO — contraseña de admin hardcodeada en texto plano, committeada a git.** `database/seeders/CreateAdminSeeder.php` crea el usuario `go@0km.app` con una contraseña literal escrita en el código fuente (`bcrypt('...')`). Ese archivo está versionado en git — cualquiera con acceso al repositorio (histórico incluido) puede leer la contraseña real del admin de producción. Es además una contraseña simple tipo passphrase, no generada aleatoriamente. **Acción recomendada inmediata**: rotar esa contraseña en producción ahora, y reemplazar el seeder para que genere una contraseña aleatoria o la lea de una variable de entorno, nunca hardcodeada.
2. **Sin `.env.example`** — ver §9.11. No es una fuga de secretos en sí, pero sí una práctica de higiene ausente que aumenta el riesgo de que alguien termine commiteando un `.env` real por no tener plantilla.
3. `APP_DEBUG=false` en producción — correcto, sin fuga de stack traces.
4. `.env` correctamente en `.gitignore` — no hay evidencia de que el archivo real esté versionado.
5. Autorización de acciones admin (`ReviewController::approve/reject`) sin `$this->authorize()` explícito — cubierto por el middleware `role:admin,super_admin` del grupo de rutas, así que no es explotable hoy, pero si alguna vez esa acción se expone fuera del grupo (o el grupo cambia), no tiene su propia red de seguridad.
6. Validación de inputs en general es consistente y correcta (`FormRequest`/`$request->validate()` en todos los controladores revisados) — no se encontraron endpoints sin validar.

---

## 13. Riesgos

- **Hosting compartido sin colas ni caché real** — cualquier feature que necesite trabajo en background (envío de emails masivos, procesamiento de imágenes al subir) requiere primero resolver infraestructura (¿worker de colas es viable en Hostinger compartido? probablemente no sin un cron que dispare `queue:work --stop-when-empty` periódicamente).
- **Deploy 100% manual** (`scp` + purga de LiteSpeed a mano) — sin CI/CD, sin tests de regresión automatizados corriendo en cada cambio. El riesgo de un deploy que rompe algo sin que nadie lo note antes de que un usuario real lo vea es real y ya se materializó al menos una vez esta sesión (bug de Inertia `Responsable`-wrapping detectado post-deploy, dos veces).
- **MariaDB spatial `NOT NULL`** — cualquier tabla nueva con columna espacial indexada debe recordar esta restricción (documentada en `docs/GEO_MARIADB.md`), o el `CREATE TABLE`/`ALTER` falla en producción de forma no obvia.
- **Escalar a "plataforma nacional" con la arquitectura actual de shared hosting** — viable para el volumen actual (decenas de organizaciones), pero si el tráfico o el volumen de datos crece un orden de magnitud, revisar si Hostinger compartido sigue siendo la base correcta (ya anotado como decisión consciente y temporal en `docs/FASE_1_README.md`).
- **Un solo desarrollador/sesión de IA construyendo secuencialmente** — sin tests automatizados que validen que un sprint no rompió el anterior; se ha dependido de revisión manual línea por línea repetidamente.

---

## 14. Código reutilizable

- **`HasGeoLocation` trait** (`app/Models/Concerns/HasGeoLocation.php`) — patrón limpio y ya usado por 8 modelos distintos. Reutilizar tal cual para cualquier entidad geoespacial nueva.
- **Patrón CRUD admin de Organización/Experiencia** (Sprints 3a/4b) — controlador + form Vue + política, replicable directamente para Project/Event/BlogPost cuando se decida darles CRUD propio (el propio Sprint 4b lo dejó explícito: "una entidad a la vez, antes de replicar").
- **Componentes públicos compartidos** `resources/js/Components/Public/{EntityHero,CategoryChips,ContactButtons,LocationMap}.vue` (Sprint 5) — ya diseñados para ser genéricos entre Operador/Experiencia; extenderlos a Proyecto/Evento es la extensión natural, ya identificada.
- **`AdminLayout.vue`** (Sprint 6) — shell puro (header + nav responsive + slot sin padding propio), ya demostró servir tanto a páginas nuevas como legacy sin fricción.
- **`SearchController`** — patrón multi-entidad con `when()` encadenado, buen molde para agregar tipos de búsqueda nuevos.
- **`MapController::geojson` + `Mapa.vue`** — patrón de capas + clustering ya genérico por `layer` key; agregar una capa nueva es mayormente agregar un `if` más en el backend y una entrada en `layerDefinitions()`.
- **`GoChileDashboardController`** — ya existe y es correcto, solo falta conectarlo (ver §7 y roadmap).

---

## 15. Funcionalidades faltantes

Contra la lista de la Fase 4 del brief:

| Necesidad | Estado |
|---|---|
| 1. Buscar rutas | ⚠️ `SearchController::searchRoutes` existe, pero `Route` es 100% rm360 (0 datos, sin liga a Organization) |
| 2. Buscar experiencias | ✅ |
| 3. Buscar proyectos | ✅ |
| 4. Buscar emprendimientos | ⚠️ Busca `Business` (0 datos) — los "emprendimientos" reales hoy son `Organization` tipo `emprendimiento`, que sí aparecen bajo "operadores" |
| 5. Buscar operadores | ✅ |
| 6. Buscar eventos | ✅ |
| 7. Explorar un mapa | ✅ |
| 8. Filtrar por territorio | ✅ (región/comuna en Explorar) |
| 9. Filtrar por categoría | ✅ |
| 10. Ver fichas individuales | ✅ para Organization/Experience/Project/Event; parcial (visual desactualizada) para Attraction/Route/Business |
| 11. Contactar proyectos | ⚠️ Proyecto muestra `how_to_collaborate` como texto, sin botones de contacto directo (a diferencia de Operador/Experiencia que sí tienen `ContactButtons`) |
| 12. Publicar contenido | ⚠️ Solo vía admin (Organization/Experience/Article); no hay flujo de autopublicación para dueños de Organization |
| 13. Mostrar proyectos de conservación | ✅ (`Project.category` incluye `conservacion`) |
| 14. Mostrar iniciativas de naturaleza | ✅ (mismo modelo `Project`) |
| 15. Mostrar experiencias turísticas | ✅ |
| 16. Mostrar actividades outdoor | ✅ |

**Ausente por completo, no en la lista original pero relevante para la visión "comunidad":**
- No hay perfil de usuario público, ni feed de actividad, ni forma de que dos usuarios/organizaciones se "conecten" entre sí más allá de mirar fichas — la pieza de "comunidad" y "conectar personas" de la visión está la menos construida de las cinco (LUGAR + PERSONA + EXPERIENCIA + TERRITORIO + COMUNIDAD).
- No hay sistema de tags transversal (solo `Article` tiene tags; `Experience`/`Organization`/`Project` no).
- No hay reseñas ni favoritos para ninguna entidad de GO Chile 2.0 (solo Business, ver §4).

---

## 16. Arquitectura propuesta

**Conservar tal cual:**
- Laravel + Inertia + Vue, sin cambiar de framework (según regla explícita).
- MariaDB + tipos espaciales nativos — no migrar a PostGIS mientras el hosting sea compartido; ya es la decisión correcta documentada.
- `HasGeoLocation`, el patrón CRUD admin, los componentes públicos compartidos, `AdminLayout`.
- `business_categories` como tabla de categorías compartida — no separar en `project_categories` u otra tabla nueva salvo que una categoría necesite metadata que `business_categories` no tenga.

**Modificar (bugs concretos, bajo riesgo, alto impacto):**
- Apuntar `admin.dashboard` a `GoChileDashboardController` (o fusionar ambos controladores en uno que muestre ambos mundos).
- Agregar `VITE_APP_NAME=GO Chile` a `.env` + redeploy del build.
- Compartir `flash` en `HandleInertiaRequests::share()`.
- Rotar la contraseña del admin y sacar el hardcode de `CreateAdminSeeder.php`.

**Agregar:**
- `.env.example` real.
- Un `DatabaseSeeder.php` que orqueste los seeders en el orden correcto.
- Rediseño de `/login`, `/dashboard`, `/atractivos/*`, `/rutas/*`, `/blog` a la paleta GO Chile (o, más barato: decidir explícitamente si esas rutas se dejan morir/redirigen a sus equivalentes nuevos en vez de rediseñarlas).
- Capa API v1 para Organization/Experience/Project si se planea consumo externo (móvil, integraciones).

**Refactorizar (cuando haya tiempo, no urgente):**
- Decidir el destino de `Article` vs `BlogPost` (¿unificar en uno, o mantener "Blog" como contenido rm360 legacy y "Bitácora" como el activo?).
- Extender `morphMap()` para incluir `organization`/`experience`, por consistencia (requiere migración de datos si ya hay filas con el FQCN largo guardado).
- Sacar `Commune::$fillable` de columnas inexistentes.

**Eliminar solo si se confirma innecesario** (nada de esto se tocó en esta auditoría, todo sigue intacto):
- `Public\HomeController` (confirmado código muerto, sin ruta que lo use) — candidato más seguro para eliminar de todos.
- El módulo SERNATUR (`sernatur_records`, `sernatur_sync_logs`) si se confirma que el catastro manual seguirá siendo la única fuente de negocios para siempre.
- `leads`, `plans`/`subscriptions`/`payments`, `audit_logs` — mientras seas conscientes de que son scaffolding sin código, no urge borrarlos; bórralos solo si algún día limpias el schema a fondo y se confirma que la monetización/auditoría no se construirá sobre esas tablas tal como están.

---

## 17. Modelo de datos propuesto

Contra la hipótesis inicial del brief, esto es lo que ya existe y cómo se reutiliza — **nada de esto se implementó en esta auditoría, es solo el diagnóstico**:

| Hipótesis | Estado real |
|---|---|
| `projects` | ✅ Ya existe (`projects`, Sprint 4a-era), con categoría fija de 4 valores — suficiente por ahora |
| `project_categories` | ❌ No existe como tabla — innecesaria mientras sean 4 categorías fijas en un enum; crear tabla solo si se necesita que un admin agregue categorías sin deploy |
| `regions`/`communes` | ✅ Ya existen, completas, con jerarquía region→province→commune→locality |
| `locations` | ❌ No existe como entidad propia — cada modelo geoespacial tiene su propia columna `location`; no hay necesidad de una tabla `locations` genérica dado el volumen actual |
| `routes` | ✅ Ya existe, pero 100% rm360 — no ligada a `Organization`. Si "rutas" es parte de la visión nacional, decidir si se migra a la familia GO Chile 2.0 o se deja como catastro aparte |
| `experiences` | ✅ Ya existe, completa |
| `events` | ✅ Ya existe, híbrida (sirve a ambas capas) |
| `articles` | ⚠️ Existen **dos**: `articles` (rm360, con SEO fields, tags, categorías) y `blog_posts` (GO Chile 2.0, más simple) — decisión pendiente, no técnica sino de producto |
| `media` | ✅ Ya existe, polimórfica, usada por la mayoría de entidades (no por `Organization`, que usa columnas planas) |
| `social_links` | ❌ No existe para Organization — Organization tiene columnas planas (`instagram`/`website`/`whatsapp`), a diferencia de `business_socials` que sí es una tabla 1:N multi-plataforma. Suficiente mientras cada organización tenga un solo Instagram/sitio/WhatsApp |
| `contacts` | ❌ Mismo caso que `social_links` — Organization no tiene tabla `contacts`, usa columnas planas |
| `collaborators` | ✅ Existe como `collaboration_requests` (postulación) + `organizations.user_id` (dueño) |
| `tags` | ⚠️ Solo existe para `Article` (`article_tags`) — no hay tagging para Experience/Organization/Project |

---

## 18. Roadmap recomendado

**Fase 0 — Arreglos inmediatos (horas, no días):**
1. Rotar contraseña de admin en producción + sacar el hardcode del seeder.
2. `VITE_APP_NAME=GO Chile` en `.env` + rebuild + deploy.
3. Apuntar `admin.dashboard` al controlador correcto.
4. Compartir `flash` en `HandleInertiaRequests`.
5. Crear `.env.example`.

**Fase 1 — Consolidación (ya en curso, Sprints 3a-7):**
6. Terminar de decidir el destino final de `Business`/`Claim`/`Article` vs sus equivalentes GO Chile 2.0 (¿se completa la fusión, o quedan como catastro legacy congelado?).
7. Unificar visualmente `/login`, `/dashboard`, `/atractivos/*`, `/rutas/*`, `/blog` a la paleta GO Chile, o decidir explícitamente retirarlas de la navegación (como ya se hizo con Businesses en Sprint 7).

**Fase 2 — Completar la visión de descubrimiento:**
8. Botones de contacto en Proyecto (reutilizar `ContactButtons.vue`).
9. Extender componentes compartidos del Sprint 5 a Evento y Proyecto.
10. Sistema de tags transversal si se quiere descubrimiento cruzado por etiqueta libre.

**Fase 3 — Comunidad (la pieza menos construida):**
11. Perfil público de usuario/colaborador.
12. Reseñas y favoritos para Organization/Experience (la infraestructura polimórfica ya está desde el Sprint 7 — falta UI y, para favoritos, ampliar `FavoriteController::$allowedTypes`).
13. Panel de autogestión para dueños de Organization (equivalente al `Owner\DashboardController` que hoy solo cubre Business).

**Fase 4 — Escala:**
14. Evaluar API v1 para GO Chile 2.0 si se planea app móvil o integraciones.
15. Evaluar necesidad de colas reales / caché en Redis si el tráfico crece más allá de lo que shared hosting + `sync` soporta cómodamente.
16. Tests automatizados de regresión, al menos sobre los flujos admin críticos (aprobar/suspender/crear/editar).

---

## 19. Prioridades — Top 10 problemas y Top 10 mejoras

**Top 10 problemas (orden de impacto):**
1. Contraseña de admin hardcodeada y committeada (seguridad, crítico).
2. Dashboard admin roto — controlador equivocado (funcional, alto impacto, fix trivial).
3. `<title>` de todo el sitio dice "Ruta Cajón del Maipo 360" (SEO/marca, alto impacto, fix trivial).
4. Mensajes de confirmación (flash) nunca llegan al frontend — afecta un flujo público core.
5. Choque de marca/paleta en `/login`, `/dashboard`, `/atractivos`, `/rutas`, `/blog`.
6. `Public\HomeController` código muerto con props incompatibles — riesgo latente si algo lo referencia por error.
7. Dos sistemas de contenido editorial en paralelo (`articles` vs `blog_posts`) sin decisión tomada.
8. API v1 no cubre ninguna entidad GO Chile 2.0.
9. Sin `.env.example` ni `DatabaseSeeder` orquestado — riesgo de onboarding/disaster-recovery.
10. Testing casi inexistente sobre una base de código que ya tiene bugs de integración reales (varios detectados y corregidos manualmente esta sesión).

**Top 10 mejoras (mayor payoff vs esfuerzo):**
1. Fase 0 completa (5 arreglos, horas de trabajo, resuelve el problema #1-4 de arriba).
2. Decidir y ejecutar la unificación visual de las páginas legacy restantes.
3. Extender `EntityHero`/`CategoryChips`/`ContactButtons`/`LocationMap` a Evento y Proyecto (bajo esfuerzo, ya existe el patrón).
4. Ampliar `FavoriteController` a `organization`/`experience` (la tabla y el modelo ya soportan esto desde el Sprint 7).
5. Construir el dashboard/autogestión de Organization Owner (analogía directa del que ya existe para Business).
6. Resolver `articles` vs `blog_posts` — probablemente congelar uno.
7. Agregar botones de contacto a Proyecto.
8. Sistema de tags transversal simple.
9. `.env.example` + `DatabaseSeeder` — bajo esfuerzo, alto valor operacional.
10. Cobertura de tests sobre los flujos admin (Organization/Experience CRUD, aprobar/suspender) — daría red de seguridad real a los próximos sprints.

---

## 20. Checklist de implementación

Nada de esto está hecho todavía — es la lista de trabajo derivada de esta auditoría, no un registro de cambios.

- [ ] Rotar contraseña admin de producción
- [ ] Reemplazar `CreateAdminSeeder.php` (sin password hardcodeada)
- [ ] Agregar `VITE_APP_NAME=GO Chile` a `.env` + rebuild + deploy
- [ ] Apuntar `admin.dashboard` a `GoChileDashboardController`
- [ ] Compartir `flash` en `HandleInertiaRequests::share()`
- [ ] Crear `.env.example`
- [ ] Crear `DatabaseSeeder.php` orquestando el orden correcto
- [ ] Decidir destino de `Public\HomeController` (eliminar si se confirma muerto)
- [ ] Decidir destino de `articles` vs `blog_posts`
- [ ] Rediseñar o retirar `/login`, `/dashboard`, `/atractivos/*`, `/rutas/*`, `/blog`
- [ ] Extender componentes compartidos (Sprint 5) a Evento y Proyecto
- [ ] Ampliar `FavoriteController::$allowedTypes` a `organization`/`experience`
- [ ] Botones de contacto en Proyecto
- [ ] Dashboard/autogestión para Organization Owner
- [ ] Sistema de tags transversal
- [ ] Limpiar `Commune::$fillable` (columnas inexistentes)
- [ ] Evaluar extender `morphMap()` a `organization`/`experience`
- [ ] Tests automatizados sobre flujos admin críticos
- [ ] Evaluar API v1 para entidades GO Chile 2.0
- [ ] Evaluar necesidad de colas/Redis según crecimiento de tráfico
- [ ] Arreglar link roto `/negocios/{slug}` → `/emprendimientos/{slug}` en `Explorar.vue`
- [ ] Agregar guard (`whenLoaded`/null-check) a `$r->business`/`$c->business` en `Admin\ReviewController`/`Admin\ClaimController` antes de que una review/claim de Organization los rompa
- [ ] Confirmar si `activities`/`Activity` sigue en uso o ya quedó superado por `Experience` (candidato a retirar como se hizo con `Business`)
- [ ] Confirmar si `Api\V1\EventController::business()` sigue siendo una relación válida tras la migración de `organization_fields` en `events`
- [ ] Agregar `<Head>` de Inertia (título/description/OG) al menos en las fichas públicas (Operador/Experiencia/Proyecto)
