# Sprint 1 — Arquitectura GO Chile

Fecha: 5 de septiembre de 2026. Documento de **diseño únicamente** — ningún modelo, migración, ruta, controlador ni tabla fue modificado para producir este documento. Todo lo que sigue está verificado leyendo el código real (`app/Models`, `app/Http/Controllers`, `routes/*.php`, `database/migrations`, `resources/js`), no inventado. Donde no pude verificar un dato (por ejemplo cantidad de filas reales en una tabla, al no tener acceso a la base de datos desde este entorno) lo digo explícitamente en vez de asumirlo.

---

## 1. Estado actual

GO Chile corre sobre dos capas en el mismo codebase Laravel + Inertia + Vue, compartiendo base de datos MariaDB:

- **Legacy (rm360 — Ruta Cajón del Maipo 360):** catastro turístico de un solo destino fijo (Cajón del Maipo). 100% funcional, **0 filas reales** en su núcleo (`businesses` y las 4 tablas satélite). `Article`/`Attraction`/`Route` viven en el mismo destino fijo. `Event` nació acá.
- **GO Chile 2.0:** modelo nacional, sin destino fijo. `Organization` es el núcleo, con ~9-10 colaboradores reales cargados (ver `docs/AUDITORIA_GO_CHILE.md §8`). `Experience`/`Project`/`BlogPost`/`CollaborationRequest` son las entidades de contenido. `Event` fue extendido para servir a ambas capas a la vez (caso único, ver §4).

Nada de esto cambia en este sprint. Este documento es el mapa para decidir, en sprints futuros, qué se fusiona, qué se adapta y qué se deja como está.

---

## 2. Entidades legacy (rm360)

| Entidad | Estado real |
|---|---|
| `Business` | **Deprecado explícitamente** (docblock agregado en Sprint 7). 0 filas. |
| `Attraction` | Funcional, sin datos, sin admin, sin owner. |
| `Route` (trekking) | Funcional, sin datos, sin admin, sin owner. Alias en comentario de código para no chocar con la fachada `Route` de Laravel. |
| `Article` (+`ArticleCategory`/`ArticleTag`/`ArticleRelation`) | **Caso especial** — es el único legacy con CRUD admin activo (`Admin\ArticleController`, rutas `/admin/articles`). No sé si tiene contenido real cargado o no (no verificable sin BD), pero el hecho de que siga teniendo UI de administración activa lo distingue del resto del legacy, que está congelado. |
| `Activity` | Sin ruta pública, sin página Vue, sin admin. Su forma (destino, dificultad, duración, ubicación, entidad que la ofrece) es casi idéntica a `Experience`. Aparenta ser un antecesor directo, huérfano. |
| `Event` (núcleo legacy) | Ver §4 — es el único caso donde legacy y GO Chile 2.0 comparten literalmente la misma tabla. |
| `Claim` | FK directa y `NOT NULL` a `business_id` (no polimórfica). Único mecanismo de asignación de dueño que existe hoy en todo el proyecto (ver §9). |
| `Locality` | Solo conectada a `Business` (`hasMany businesses`). Sin uso en GO Chile 2.0 — `Organization` no tiene `locality_id`. |
| `SernaturRecord` / `sernatur_sync_logs` | Módulo de importación, documentado como inactivo en su propio código. |
| `business_services` / `business_locations` / `business_contacts` / `business_socials` | 1:N desde `Business`, 0 filas, sin equivalente en `Organization` (que usa columnas planas). |
| `Verification` | Tabla de auditoría de verificación, solo `Business`. Solapa conceptualmente con la columna plana `verification_status` que `Organization` ya tiene desde Sprint 7 — dos mecanismos distintos para la misma idea. |
| `leads`, `plans`/`subscriptions`/`payments`, `audit_logs`, `notifications` | Scaffolding sin modelo Eloquent (o sin código que lo use) — no forman parte del sistema activo hoy. |

## 3. Entidades GO Chile 2.0

| Entidad | Estado real |
|---|---|
| `Organization` | Núcleo. CRUD admin completo (listar/aprobar/suspender/editar). Landing pública completa (`/operadores/{slug}`). Sin self-service para el dueño (ver §9). |
| `Experience` | CRUD admin completo (Sprint 4b — el único con create+edit+delete de las entidades nuevas). Landing pública completa con componentes compartidos (Sprint 5). |
| `Project` | Landing pública completa, **sin ningún CRUD admin** — no existe `Admin\ProjectController` ni página Vue para crearlo/editarlo (confirmado por `find`, no hay archivo). Hoy un `Project` solo puede existir si se inserta directo en la base de datos. |
| `BlogPost` | Landing pública completa (`/bitacora`), **sin ningún CRUD admin** (confirmado — no existe `Admin\BlogPostController`). Mismo problema que `Project`. |
| `CollaborationRequest` | El único punto de entrada real hacia `Organization` — formulario público sin autenticación, aprobado por admin, que crea la `Organization`. Ver el hallazgo importante en §9. |
| `Event` (extendido) | Ver §4. |
| `Region`/`Province`/`Commune`/`Locality` | Compartido — jerarquía territorial usada tanto por legacy como por GO Chile 2.0 (aunque `Locality` en la práctica solo la usa `Business`). |
| `business_categories` | Compartido y activo — ver §7. |

---

## 4. Matriz de consolidación (Fase 1)

| Entidad | Tabla | Modelo | Controlador | Rutas públicas | Admin | Uso real | Estado |
|---|---|---|---|---|---|---|---|
| Business | `businesses` | `Business.php` | `Public\BusinessShowController`, `Owner\BusinessEditController`, `Admin\BusinessController` (solo index), `Api\V1\BusinessController` | `/emprendimientos/{slug}` | `/admin/businesses` (sin link en nav desde Sprint 7) | **0 filas confirmado** (repetido en toda la sesión) | Legacy — deprecado |
| Organization | `organizations` | `Organization.php` | `OrganizationController` (public show), `Admin\OrganizationController` (CRUD) | `/operadores/{slug}` | `/admin/organizaciones` | ~9-10 colaboradores reales (histórico Sprint 2, sin confirmar cambios posteriores) | Activo — núcleo GO Chile 2.0 |
| Experience | `experiences` | `Experience.php` | `ExperienceController` (public show), `Admin\ExperienceController` (CRUD completo) | `/experiencias/{slug}` | `/admin/experiencias` | No verificable sin BD | Activo — CRUD completo |
| Project | `projects` | `Project.php` | `ProjectController` (solo show) | `/proyectos/{slug}` | **Ninguno** | No verificable sin BD | Parcial — sin gestión admin |
| Route | `routes` | `Route.php` | `Public\RouteShowController`, `Api\V1\RouteController`, `Api\V1\RoutePointController` | `/rutas/{slug}` | Ninguno | 0 filas (mismo patrón que Business) | Legacy — funcional sin datos |
| Attraction | `attractions` | `Attraction.php` | `Public\AttractionShowController`, `Api\V1\AttractionController` | `/atractivos/{slug}` | Ninguno | 0 filas | Legacy — funcional sin datos |
| Article | `articles`(+categories/tags/relations) | `Article.php` | `Public\BlogIndexController`/`BlogShowController`, `Admin\ArticleController` (CRUD), `Api\V1\ArticleController` | `/blog`, `/blog/{slug}` | `/admin/articles` | No verificable sin BD, pero CRUD admin activo (a diferencia del resto del legacy) | Legacy — caso especial, no congelado |
| BlogPost | `blog_posts` | `BlogPost.php` | `BlogPostController` (public) | `/bitacora`, `/bitacora/{slug}` | **Ninguno** | No verificable sin BD | Activo en frontend — sin gestión admin |
| Event | `events` | `Event.php` | `EventController` (public), `Api\V1\EventController` | `/agenda`, `/agenda/{slug}` | Ninguno | No verificable sin BD | Híbrido — sirve ambas capas a la vez |
| Activity | `activities` | `Activity.php` | `Api\V1\ActivityController` únicamente | Ninguna | Ninguno | No verificable, sin ruta/página que lo consuma | Legacy — huérfano, candidato a confirmar-y-retirar |
| User | `users` | `User.php` | `Auth\LoginController`, `Api\V1\Auth\*`, `Owner\DashboardController` | `/login`, `/dashboard` | — | Admin(s) + usuarios registrados | Activo — núcleo de auth |
| Claim | `claims` | `Claim.php` | `Owner\ClaimController`, `Admin\ClaimController`, `Api\V1\ClaimController` | `/emprendimientos/{slug}/reclamar` | `/admin/claims` | Ligado a Business → 0 filas esperadas | Legacy — Business-only, no polimórfico |
| Review | `reviews` | `Review.php` | `Admin\ReviewController`, `Api\V1\ReviewController` | — | `/admin/reviews` | No verificable | Polimórfico a medias (ver §8) |
| Favorite | `favorites` | `Favorite.php` | `Api\V1\FavoriteController` | — | — | No verificable, sin UI web | Polimórfico a medias (ver §8) |
| Gallery | `galleries` | `Gallery.php` | Ninguno | — | — | **Sin ningún código que cree filas** | Sin uso — scaffolding sin conectar |
| Category (`business_categories`) | `business_categories` | `BusinessCategory.php` | — | — | — | 16 categorías activas (post-consolidación Sprint 7) | Activo — compartido, ver §7 |
| Region | `regions` | `Region.php` | — | — | — | Jerarquía territorial completa | Activo |
| Province | `provinces` | `Province.php` | — | — | — | — | Activo |
| Commune | `communes` | `Commune.php` | — | — | — | — | Activo (con un bug menor conocido: `$fillable` referencia `centroid_lat`/`centroid_lng`, columnas que no existen en la tabla — inofensivo hoy, nada las asigna) |
| Locality | `localities` | `Locality.php` | — | — | — | Solo usada por `Business` | Legacy — sin conexión a GO Chile 2.0 |

### Fase 2 — Propuesta canónica por entidad

| Entidad | Propuesta | Justificación |
|---|---|---|
| **Business vs Organization** | Business: **E) mantener temporalmente como legacy**, ya marcado deprecado. Organization: **A) conservar**. | Ya decidido y ejecutado en Sprint 7 (fusión de campos útiles, sin borrar tabla). No hay nada nuevo que decidir acá — este sprint solo lo documenta formalmente. |
| **Article vs BlogPost** | **C) fusionar conceptualmente**, con `BlogPost` como destino final — pero **D) reemplazar gradualmente**, no de golpe. | Son el mismo concepto (contenido editorial) con dos implementaciones paralelas. `Article` tiene ventajas reales que `BlogPost` no tiene (SEO fields `meta_title`/`meta_description`, sistema de tags, soft deletes) — cualquier fusión debe llevarse esas capacidades a `BlogPost`, no perderlas. Mientras tanto: **conservar ambas rutas funcionando** (`/blog` y `/bitacora` no son duplicados desde la perspectiva del usuario, son dos secciones con distinta identidad visual). Decisión de producto pendiente: ¿se anuncia `/blog` como retirado y todo el contenido futuro va a `/bitacora`? Eso no es una decisión técnica, es de contenido/negocio — no me corresponde tomarla acá. |
| **Activity vs Experience** | Activity: **D) reemplazar gradualmente** (candidata a retiro una vez confirmado que no se usa). Experience: **A) conservar**. | La forma de `Activity` (destino, dificultad, duración, ubicación, entidad que la ofrece) es casi un calco de `Experience`, pero ligada a `business_id` en vez de `organization_id`, sin ruta pública ni página Vue que la sirva. Todo indica que `Experience` ya la superó. Antes de tocarla: confirmar con una consulta directa a la API (`GET /api/v1/activities`) o a la base si algún consumidor externo la usa. |
| **Attraction vs Project** | **No son duplicados — son conceptos distintos.** Attraction: **B) adaptar** hacia el rol de "LUGAR/ATRACTIVO" del modelo conceptual (§5). Project: **A) conservar** como "PROYECTO". | El brief pide comparar estos dos, pero conceptualmente no compiten: `Attraction` es un punto geográfico de interés (mirador, laguna, glaciar — no tiene dueño, no tiene "cómo colaborar"); `Project` es una iniciativa con una `Organization` detrás y un objetivo de conservación/impacto. Forzar una fusión sería inventar una relación que no existe en el código ni en el dominio. Lo que sí falta: `Attraction` no tiene `organization_id` (no puede asociarse a quién la gestiona) y sigue atada a un `Destination` fijo — esa es la adaptación real pendiente, no una fusión con `Project`. |
| **Route vs Experience** | **Tampoco son duplicados.** Route: **B) adaptar** hacia "RUTA" del modelo conceptual. Experience: **A) conservar** como "EXPERIENCIA". | Mismo caso que el anterior: `Route` es un recorrido geográfico (una polilínea con waypoints); `Experience` es algo que una `Organization` ofrece y se puede reservar/vivir. Una experiencia puede *transcurrir sobre* una ruta, pero no es lo mismo. La adaptación real: `Route` no tiene `organization_id` tampoco, y sus `route_points` polimórficos no incluyen `Organization`/`Experience` como posibles waypoints (ver §8) — ahí es donde sí hay un puente natural a construir, no fusionando las entidades sino conectándolas. |
| **Event legacy vs Event GO Chile** | **Ya está resuelto — es una sola tabla, un solo modelo, con doble FK opcional (`business_id` nullable + `organization_id` nullable).** Estado: **A) conservar tal cual.** | Esta es la única entidad de las listadas que YA está consolidada correctamente — la migración `alter_events_add_organization_fields` (Sprint 4a-era) hizo exactamente lo que Business/Attraction/Route todavía no tienen: agregar la FK nueva sin duplicar la tabla. Es el precedente a replicar para el resto. Único bug real encontrado: `Api\V1\EventController` hace `->with('business')` pero no `->with('organization')`, así que eventos ligados a una Organization llegan con datos incompletos por esa vía (no rota, incompleta — confirmado leyendo el archivo, ya no es una sospecha). |
| **Categories / business_categories** | **A) conservar como sistema transversal**, con adaptación futura (ver §7). | Ya activa y compartida por 3 entidades distintas. No crear `project_categories` ni ninguna tabla paralela. |
| **Claims** | **E) mantener temporalmente como legacy**, sin extender a Organization todavía. | No polimórfico, `business_id` `NOT NULL`. Convertirlo requeriría una migración real (columnas `claimable_type`/`claimable_id`, backfill, mantener `business_id` como fallback igual que se hizo con `reviews`). Es exactamente el patrón que ya se usó para `reviews` — reproducible, pero es trabajo de un sprint futuro, no de este. |
| **Reviews** | **B) adaptar** — la tabla y el modelo ya son polimórficos, `Organization` ya tiene `reviews()` (Sprint 7). Falta la mitad de "arriba": ningún endpoint permite crear una review de Organization/Experience todavía. | `Api\V1\ReviewController::store()` solo acepta `businesses/{slug}/reviews`. Ampliarlo es codificación nueva, no arquitectura — queda para el sprint de implementación. |
| **Favorites** | **B) adaptar** — mismo caso que Reviews: la tabla es polimórfica desde el inicio, `Organization` ya tiene `favoritedBy()`, pero `Api\V1\FavoriteController`'s `allowedTypes` no incluye `organization`/`experience`. | Sin UI web en absoluto para favoritos, en ninguna entidad — es una funcionalidad completamente ausente del lado del usuario final, no solo un gap de API. |
| **Gallery** | **E) mantener temporalmente**, sin uso, no estorba. | Nada crea filas ahí. No es prioridad tocarlo en ningún sentido — ni fusionar, ni borrar, ni extender. |

---

## 5. Modelo conceptual GO Chile (Fase 3)

Mapeo del modelo conceptual pedido contra lo que **ya existe hoy**, sin inventar tablas nuevas:

| Concepto | Tabla(s) que ya lo resuelven | ¿Falta algo? |
|---|---|---|
| **ORGANIZACIÓN** — quién desarrolla la actividad/proyecto | `organizations` | No — completo para el alcance actual. |
| **EXPERIENCIA** — qué puede vivir/hacer una persona | `experiences` | No — completo, con CRUD admin. |
| **RUTA** — recorrido geográfico | `routes` + `route_points` | Adaptar (§4): agregar `organization_id` nullable, y permitir que `route_points.pointable_type` acepte `Organization`/`Experience` además de lo legacy. No requiere tabla nueva. |
| **LUGAR / ATRACTIVO** — territorio o punto de interés | `attractions` | Adaptar (§4): agregar `organization_id` nullable, y evaluar si debe dejar de depender de un `Destination` fijo para tener alcance nacional (mismo cambio conceptual que ya se le hizo a `Organization` vs `Business`). No requiere tabla nueva. |
| **PROYECTO** — conservación, naturaleza, educación, cultura, territorio, impacto | `projects` | Falta CRUD admin (§4) — la tabla ya modela esto correctamente (`category` enum: conservación/geología/biodiversidad/comunidad, `how_to_collaborate`). |
| **EVENTO** — actividad temporal | `events` | Ya resuelto (§4) — es el ejemplo a seguir. |
| **BITÁCORA** — contenido editorial | `blog_posts` (+ `articles` como paralelo legacy, ver §4) | Falta CRUD admin para `blog_posts`. |
| **TERRITORIO** — región, provincia, comuna, localidad | `regions`→`provinces`→`communes`→`localities` | Ya completo y jerárquico. Único gap: `Organization`/`Experience`/`Project` no usan `Locality` (se quedan en el nivel de `Commune`) — no es un problema, es una decisión de granularidad ya tomada implícitamente. |

**Conclusión de Fase 3: no hace falta crear ninguna tabla nueva.** Las 8 piezas del modelo conceptual ya tienen una tabla real detrás. El trabajo pendiente es exclusivamente **adaptar** (agregar `organization_id` a `Attraction`/`Route`, dar CRUD admin a `Project`/`BlogPost`) — nunca **crear desde cero**.

---

## 6. Estrategia de URLs (Fase 4)

**No se implementa ninguna redirección en este sprint.** Matriz de análisis:

| URL legacy | Sirve hoy | Equivalente GO Chile 2.0 | Recomendación |
|---|---|---|---|
| `/emprendimientos/{slug}` | `BusinessShowController`, 0 datos | `/operadores/{slug}` | **Conservar tal cual.** Con 0 filas no hay nada que redirigir — un 301 hoy sería prematuro y sin contenido real que mapear. Revisar de nuevo si `Business` alguna vez llega a tener datos reales. |
| `/atractivos/{slug}` | `AttractionShowController`, 0 datos | Ninguno todavía (no existe landing pública de "lugar" en GO Chile 2.0) | **Conservar.** No hay a dónde redirigir — `Attraction` es la única pieza del modelo conceptual (§5) sin un equivalente GO Chile 2.0 construido todavía. |
| `/rutas/{slug}` | `RouteShowController`, 0 datos | Ninguno todavía | **Conservar**, mismo motivo. |
| `/blog`, `/blog/{slug}` | `Article`, CRUD admin activo | `/bitacora`, `/bitacora/{slug}` | **Alias/compatibilidad temporal** — son las dos únicas rutas legacy con posible contenido real y gestión activa. Si en un sprint futuro se decide unificar `Article`→`BlogPost` (§4), ahí sí correspondería un 301 real, slug por slug, con el contenido migrado. Hasta entonces, **no tocar**. |

Ninguna URL de GO Chile 2.0 (`/operadores/*`, `/experiencias/*`, `/proyectos/*`, `/agenda*`, `/bitacora*`) requiere cambios — son el destino final, no el origen, de cualquier estrategia de redirección futura.

---

## 7. Estrategia de categorías (Fase 5)

**Tablas existentes:** `business_categories` (`id, name, slug, icon, map_layer, parent_id` — jerárquica vía auto-referencia), 16 filas activas tras la consolidación del Sprint 7.

**Relaciones actuales (verificado en código):**
- `businesses.business_category_id` → FK directa 1:N (legacy).
- `organizations` ↔ `business_categories` vía pivot `organization_categories` (N:N).
- `experiences.activity_type_id` → FK directa N:1.
- `attractions.category` → **string libre, sin FK** (valores como "mirador", "laguna", "glaciar" tal cual, sin tabla detrás).
- `projects.category` → **enum fijo de 4 valores** (`conservacion`/`geologia`/`biodiversidad`/`comunidad`), sin FK a `business_categories`.
- `blog_posts.category` → **enum fijo de 6 valores**, sin FK.
- `events.category` → **enum fijo de 7 valores**, sin FK.

**Problema real:** `business_categories` funciona bien para las 3 entidades que sí la usan (Business/Organization/Experience), pero **4 entidades más (Attraction, Project, BlogPost, Event) implementan su propio sistema de categorización paralelo**, cada una con su propia forma (string libre, o enum fijo distinto). Esto significa que hoy "categoría" no es un concepto unificado en todo GO Chile — es cinco sistemas distintos que casualmente comparten el nombre del campo.

**¿Puede `business_categories` funcionar como sistema transversal?** Sí, técnicamente — la tabla ya no tiene nada de "negocio" en su estructura (es genérica: nombre, slug, ícono, capa de mapa, jerarquía). El nombre de tabla es heredado, no una limitación real.

**Propuesta futura (no implementar ahora):** convertir el vínculo en una relación polimórfica N:N (`categorizables`, con `categorizable_type`/`categorizable_id` + `business_category_id`), reemplazando gradualmente los enums fijos de `Project`/`BlogPost`/`Event` y el string libre de `Attraction`. Esto **sí requiere una migración nueva** (una tabla pivote polimórfica) — no es gratis, pero es la única forma de unificar sin duplicar `business_categories` en 4 tablas nuevas (`project_categories`, etc., que el brief explícitamente pide evitar). No crear esas tablas duplicadas bajo ninguna circunstancia — si se avanza en esto, es la tabla polimórfica o nada.

---

## 8. Estrategia de polimorfismo (Fase 6)

**`morphMap()` actual** (`AppServiceProvider`, verificado):
```php
Relation::morphMap([
    'business' => \App\Models\Business::class,
    'attraction' => \App\Models\Attraction::class,
    'activity' => \App\Models\Activity::class,
    'route' => \App\Models\Route::class,
    'article' => \App\Models\Article::class,
    'destination' => \App\Models\Destination::class,
]);
```
Sin `enforceMorphMap()` — deliberado, documentado en un comentario del propio archivo (Sanctum y otros paquetes de terceros usan relaciones polimórficas propias que romperían si se forzara).

**Relaciones polimórficas reales y su estado:**

| Relación | Tipo | Incluye legacy | Incluye GO Chile 2.0 | Inconsistencia |
|---|---|---|---|---|
| `reviews.reviewable_type/id` | `morphTo`/`morphMany` | Sí (vía `business_id` legacy + alias `'business'`) | Sí (`Organization::reviews()`, Sprint 7) — **pero sin alias en morphMap** | Los registros nuevos guardan el FQCN completo (`App\Models\Organization`) en vez de un alias corto — funciona, pero mezcla dos convenciones en la misma columna. |
| `favorites.favoritable_type/id` | `morphTo`/`morphMany` | Sí (alias implícito, aunque `Favorite` no está en absoluto en el listado de arriba — revisando: `favorites` nunca tuvo `business_id` legacy, siempre fue polimórfica) | Sí (`Organization::favoritedBy()`, Sprint 7) — mismo problema, sin alias | Igual que arriba. |
| `route_points.pointable_type/id` | `morphTo` | Sí (Business/Attraction/Activity) | **No** | `Organization`/`Experience` no pueden ser waypoint de una ruta hoy — gap real para cuando se conecte Route↔Experience (§5). |
| `article_relations.relatable_type/id` | `morphTo` | Sí (Business/Attraction/Route/Activity/Destination) | **No** | Mismo gap — un `Article` no puede referenciar una `Organization`/`Experience`/`Project` como contenido relacionado. |
| `media.mediable_type/id` | `morphTo`/`morphMany` | Sí (Business/Attraction/Event/Project — sí incluye Project) | Parcial | `Experience` y `Project` SÍ usan `media` (`morphMany`). `Organization` **no** — usa columnas planas `logo_url`/`cover_image` en vez de la tabla `media`. Inconsistencia real: dos entidades del mismo nivel conceptual (Organization vs Experience/Project) resuelven "imágenes" de dos formas distintas. |
| `claims.business_id` | FK directa, **no polimórfica** | Solo Business | No | Ver §4 — requiere migración real para polimorfizar, no se hace en este sprint. |

**Propuesta única (para un sprint futuro, no ahora):**
1. Agregar `'organization' => Organization::class`, `'experience' => Experience::class`, `'project' => Project::class` al `morphMap()` — cambio de una línea, pero **requiere backfill** de las filas `reviews`/`favorites` que ya se hayan guardado con el FQCN completo (si las hay), para no dejar datos con dos formatos distintos conviviendo.
2. Extender `route_points` y `article_relations` para aceptar los mismos 3 alias nuevos, sin tocar su estructura (son polimórficas, solo falta que el código que las escribe/lee contemple los nuevos tipos).
3. Decidir, en algún momento, si `Organization` migra sus imágenes a la tabla `media` (consistencia) o si en cambio `Experience`/`Project` migran a columnas planas (simplicidad) — hoy conviven ambos enfoques sin una razón documentada para la diferencia.

**No se cambia nada del sistema polimórfico en este sprint**, tal como pide el brief.

---

## 9. Estrategia de usuarios / owners (Fase 7)

**Cómo funciona hoy la propiedad de un `Business`** (funcional, aunque 0 filas reales):
1. Un usuario autenticado hace `POST /emprendimientos/{slug}/reclamar` → `Owner\ClaimController::store()` crea una fila `Claim` con `user_id` = el usuario, `business_id` = el negocio, `status='pending'`.
2. Un admin aprueba (`Admin\ClaimController::approve()`) → **acá es donde se asigna el dueño**: `$claim->business->update(['claim_status' => 'claimed', 'owner_id' => $claim->user_id])`.
3. Desde ese momento, `Owner\DashboardController` (`/dashboard`) y `Owner\BusinessEditController` (`/dashboard/negocios/{slug}/editar`) funcionan porque filtran por `Business::where('owner_id', $user->id)`.

**Cómo funciona hoy la propiedad de una `Organization`:** **no existe ningún mecanismo.**

Esto es el hallazgo más importante de esta fase, y lo verifiqué directamente leyendo `CollaborationRequest::approve()`:

```php
public function approve(): Organization
{
    $organization = Organization::create([
        'type' => $this->type,
        'name' => $this->name,
        // ... 'user_id' NUNCA aparece acá
    ]);
    ...
}
```

`collaboration_requests` (la tabla del formulario público "Quiero ser parte") **ni siquiera tiene una columna `user_id`** — la ruta pública `POST /colaboradores` no exige autenticación (confirmado en `routes/web.php`, está fuera de cualquier grupo `auth`). Es decir: **hoy es estructuralmente imposible que una `Organization` recién creada tenga un dueño asignado**, porque el único camino que existe para crear una (`CollaborationRequest::approve()`) nunca setea `organizations.user_id` — la columna existe (`nullable`, agregada desde el diseño original) pero ningún código la escribe jamás.

**Consecuencia concreta, no teórica:** `ExperiencePolicy::create()` y `ProjectPolicy::create()` tienen esta lógica:
```php
return $user->organizations()->where('status', 'approved')->exists() || $user->hasRole('admin');
```
Como ninguna `Organization` tiene nunca un `user_id`, `$user->organizations()` siempre devuelve una colección vacía para cualquier usuario que no sea admin — **la mitad "dueño de organización aprobada puede crear su propia Experience/Project" de esa policy es código muerto en la práctica**, aunque esté perfectamente bien escrita. Solo un admin puede crear Experiences/Projects hoy, pese a que el código está diseñado para permitir más que eso.

**Qué falta (diseño, no implementación):**
1. Un mecanismo de asignación de dueño para `Organization`, equivalente al `Claim` de `Business` pero sin necesitar duplicar esa tabla — dos caminos razonables: (a) agregar un campo opcional `user_id`/email al formulario de `CollaborationRequest` para que quien postula quede vinculado desde el origen, y `approve()` lo traspase a `organizations.user_id`; o (b) un flujo de "reclamar esta organización" análogo al de Business, reutilizando el patrón `Claim` pero polimórfico (conecta con §8, punto 1 de la propuesta). No decido acá cuál — es una decisión de producto con implicaciones de UX (¿el formulario público debe pedir login primero?).
2. Un `Owner\OrganizationController` (o extender el existente `Owner\DashboardController`) para que un dueño real pueda editar su propia `Organization` sin pasar por el admin — hoy **el único camino para editar una Organization es `Admin\OrganizationController`**, sin importar quién sea el dueño.
3. Ninguna `Policy` de `Attraction`/`Route`/`Activity`/`Event` existe todavía (verificado — solo hay Policy para Article, BlogPost, Business, CollaborationRequest, Experience, Organization, Project) — si esas entidades legacy alguna vez ganan un dueño real, van a necesitar su propia Policy antes.

**No implementar nada de esto todavía** — queda documentado para decidir el diseño exacto en un sprint futuro.

---

## 10. Qué se conserva

`Organization`, `Experience`, `Project`, `BlogPost`, `Event` (tal cual, es el modelo a replicar), toda la jerarquía territorial (`Region`/`Province`/`Commune`/`Locality`), `business_categories` como sistema de categorías, todas las rutas públicas legacy y GO Chile 2.0 tal como están hoy, `HasGeoLocation`, el patrón CRUD admin ya usado en Organization/Experience, los componentes públicos compartidos (`EntityHero`/`CategoryChips`/`ContactButtons`/`LocationMap`), `AdminLayout`.

## 11. Qué se adapta

`Attraction` y `Route` (agregar `organization_id` nullable, evaluar independencia del `Destination` fijo), `route_points`/`article_relations`/`media`/`morphMap` (extender a Organization/Experience/Project, con backfill donde corresponda), `Claim` (eventual polimorfismo, mismo patrón que ya se usó en `reviews`), `Review`/`Favorite` (completar el lado de escritura/UI que falta), `Attraction.category`/`Project.category`/`BlogPost.category`/`Event.category` (eventual migración a `business_categories` vía tabla polimórfica).

## 12. Qué se deprecia

`Business` (ya deprecado formalmente desde Sprint 7 — este sprint no cambia esa decisión). `Activity`, candidata a deprecar una vez confirmado que ningún consumidor externo usa `GET /api/v1/activities`. `Article`, condicionalmente — solo si en algún sprint futuro de producto se decide que `BlogPost`/Bitácora reemplaza por completo al Blog viejo (decisión no tomada acá).

## 13. Qué NO se debe tocar todavía

Ninguna tabla física (sin `DROP`), ninguna migración destructiva, ninguna URL pública existente (legacy o GO Chile 2.0), el sistema polimórfico tal como está (§8, explícitamente pedido por el brief), `business_categories` como tabla (no crear duplicados, no migrar todavía a polimórfica), el flujo de `Claim` para Business (sigue siendo el único mecanismo de ownership funcional del proyecto), `deploy-sprint6.ps1`/`deploy-sprint7.ps1` (no ejecutados, sin relación con este sprint).

## 14. Roadmap de implementación (orden sugerido, para sprints futuros — no este)

1. **CRUD admin para `Project` y `BlogPost`** — son las dos entidades GO Chile 2.0 con landing pública completa pero sin forma de administrarse; es el hueco más visible y de menor riesgo de tocar (mismo patrón ya probado en Organization/Experience).
2. **Diseño + implementación del mecanismo de ownership de `Organization`** (§9) — desbloquea que las Policies ya escritas (`ExperiencePolicy`/`ProjectPolicy`) empiecen a funcionar como fueron diseñadas, sin tocar esas Policies.
3. **Completar Reviews/Favorites para Organization/Experience** (§8) — la infraestructura ya existe desde Sprint 7, falta el lado de API/UI.
4. **Agregar `organization_id` a `Attraction` y `Route`** (§5/§11) — adaptación de esquema, no fusión, de bajo riesgo (columna nullable nueva).
5. **Extender `morphMap`, `route_points`, `article_relations`, `media`** a Organization/Experience/Project (§8) — requiere backfill cuidadoso, hacerlo después de que haya datos reales que backfillear tenga sentido.
6. **Decisión de producto sobre `Article` vs `BlogPost`** (§4/§6) — no es un paso técnico, es alinear con Nahuel qué pasa con `/blog` a largo plazo, antes de tocar código.
7. **Categorías transversales vía tabla polimórfica** (§7) — el cambio de mayor alcance de esta lista, dejarlo para el final, cuando el resto ya esté estable.

---

## Hallazgos de seguridad / riesgo de pérdida de datos encontrados durante este sprint

Ninguno nuevo. Se investigó código, no se tocó nada, no se encontró ningún riesgo inmediato de seguridad ni de pérdida de datos que amerite detenerse antes de terminar el documento (tal como pide el brief, de haber encontrado algo así, me hubiera detenido a reportarlo antes de seguir).
