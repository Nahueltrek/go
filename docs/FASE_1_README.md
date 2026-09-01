# Ruta Cajón del Maipo 360 — Fase 1: proyecto Laravel + ERD en código

## Historial de esta fase (importante para entender por qué está como está)

Esta fase pasó por dos versiones:
1. **Versión original** — diseñada para PostgreSQL + PostGIS en un VPS con Docker, según el plan maestro.
2. **Versión actual** — reescrita después de confirmar por SSH que `rm360.0km.app` corre en **hosting compartido** de Hostinger (PHP 8.3.30, MariaDB 11.8, sin Docker, sin root, sin PostgreSQL, sin servidor Redis). El cambio de arquitectura no fue opcional: es lo único que corre en el entorno real disponible.

Ver `docs/GEO_MARIADB.md` para el detalle completo del reemplazo geoespacial.

## Qué incluye esta entrega

- Proyecto Laravel 13 completo: `composer.json`, `artisan`, `bootstrap/app.php`, `config/*`, frontend base con Inertia + Vue 3 + Tailwind 4.
- `database/migrations/` — 37 migraciones, en orden de dependencias correcto, con tipos espaciales nativos de MariaDB (`point`, `polygon`, `lineString`) en vez de PostGIS.
- `database/seeders/` — roles/permisos base y el destino piloto Cajón del Maipo.
- `infra/HPANEL_DEPLOY.md` — guía real de deploy para este hosting compartido.
- `infra/docker-compose.yml`, `infra/Dockerfile`, `infra/VPS_SETUP.md` — quedan como referencia para una eventual migración futura a VPS, **no aplican hoy**.

## Decisiones técnicas (versión MariaDB)

1. **Sin extensión que habilitar** — a diferencia de PostGIS, los tipos espaciales de MariaDB (`POINT`, `POLYGON`, `LINESTRING`) son nativos del motor, no requieren una migración de `CREATE EXTENSION`.
2. **Columnas espaciales con el schema builder de Laravel** (`$table->point(...)`, `$table->polygon(...)`, `$table->lineString(...)`, todas con `srid: 4326`), no con `DB::statement` crudo como en la versión Postgres.
3. **Restricción real de MariaDB: las columnas con índice `SPATIAL` deben ser `NOT NULL`.** Esto obligó a una decisión por tabla:
   - `businesses.location`, `business_locations.point`, `attractions.location` → **NOT NULL + índice spatial** (se asume que siempre hay coordenadas al crear el registro, razonable para un catastro manual).
   - `destinations.boundary`, `communes.boundary`, `routes.path` → **nullable, sin índice spatial** (pueden crearse antes de tener el polígono/traza dibujado — de hecho el propio seeder de Cajón del Maipo no carga un `boundary` todavía).
   - `activities.location`, `localities.center`, `events.location` → nullable, sin índice (igual que en la versión original, nunca tuvieron índice).
4. **`businesses` no depende de `sernatur_records` como requisito** — el campo `sernatur_record_id` es nullable, y el catastro puede vivir 100% con `source = admin`, tal como se decidió en la Fase 0.
5. **Cache, sesiones y colas usan tablas de base de datos** (`cache`, `cache_locks`, `sessions`, `jobs`), no Redis — no hay servidor Redis disponible en este hosting, solo la extensión PHP sin nada corriendo detrás.
6. **Trazabilidad de origen** (`source`, `source_url`, `source_type`, `source_record_id`, `imported_at`, `last_synced_at`) en `businesses`, `attractions` y `sernatur_records`.
7. **Relaciones polimórficas** (`route_points`, `article_relations`, `media`, `galleries`, `favorites`) con `{tabla}able_type`/`{tabla}able_id` nombrados a mano según el documento maestro; `notifications` usa el estándar `notifiable` de Laravel.
8. **`destinations.boundary`** sigue siendo el mecanismo central de la arquitectura multi-destino — "Cajón del Maipo" existe solo como una fila sembrada por `CajonDelMaipoDestinationSeeder`, nunca como texto en el código.
9. **Softdeletes** en `businesses`, `attractions`, `activities`, `routes`, `articles`, `events` — no en tablas de trazabilidad o pivotes.

## Cómo probar

Ver `infra/HPANEL_DEPLOY.md` para el paso a paso completo real. Resumen:

```bash
cd /home/u451636252/domains/rm360.0km.app
composer install --no-dev --optimize-autoloader
cp .env.example .env
# completar DB_DATABASE/DB_USERNAME/DB_PASSWORD reales del panel
php artisan key:generate
php artisan migrate --force
php artisan db:seed --class="Database\Seeders\RolesAndPermissionsSeeder" --force
php artisan db:seed --class="Database\Seeders\CajonDelMaipoDestinationSeeder" --force
```

Verificación mínima:
```bash
php artisan migrate:status
mysql -u ... -p -e "SELECT slug FROM <tu_base>.destinations;"  # debe mostrar 'cajon-del-maipo'
```

## Qué falta (pendiente, no incluido en esta fase)

- Models Eloquent más allá de `User`/`Role`/`Permission` (relaciones, scopes geográficos con las queries de `docs/GEO_MARIADB.md`, casts).
- Policies por modelo (Fase 6).
- Factories de testing.
- El módulo `Services/Sernatur/` en sí (tablas listas, sin lógica de importación activa).
- Confirmar si el hosting tiene Node/npm para `npm run build`, o si el build de frontend siempre se hace local y se sube por SFTP.
