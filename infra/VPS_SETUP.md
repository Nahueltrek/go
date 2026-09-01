# Setup del VPS — rm360.0km.app

> ⚠️ **No aplica al hosting actual.** Se confirmó por SSH que `rm360.0km.app`
> vive en un hosting **compartido** de Hostinger (sin Docker, sin root, sin
> PostgreSQL), no en un VPS. Esta guía queda como referencia para una eventual
> migración futura a un VPS real. Para el deploy actual, ver
> `infra/HPANEL_DEPLOY.md`.

Todo esto se ejecuta por SSH en el VPS. No lo puedo correr yo directamente — no tengo acceso a tu servidor — así que lo dejo como checklist exacto para que lo pegues vos.

## 0. Requisitos previos
- Acceso SSH root (o usuario con sudo) al VPS de Hostinger.
- El subdominio `rm360.0km.app` ya apuntando (registro A) a la IP del VPS.

## 1. Instalar Docker y Docker Compose

```bash
curl -fsSL https://get.docker.com | sh
sudo systemctl enable docker --now
sudo usermod -aG docker $USER
# cerrar sesión SSH y volver a entrar para que el grupo docker tome efecto

docker --version
docker compose version
```

## 2. Subir el proyecto al VPS

Desde tu máquina local (no desde el navegador de archivos de Hostinger):

```bash
scp -r ruta360/ usuario@TU_IP_VPS:/opt/ruta360
# o, si preferís, clonar desde un repo git una vez que exista
```

En el VPS:
```bash
cd /opt/ruta360
```

El proyecto Laravel ya viene armado en esta entrega (composer.json, migraciones, seeders, frontend base con Inertia+Vue). El `docker-compose.yml` vive en `infra/` pero construye la imagen desde la raíz del proyecto (`context: ..`), así que los comandos de Docker se corren **desde adentro de `infra/`**, no desde la raíz.

## 3. Configurar el .env

```bash
cp infra/.env.docker.example .env
nano .env
```
Cambiá al menos `DB_PASSWORD` por una clave real y fuerte.

## 4. Levantar los contenedores

```bash
cd /opt/ruta360/infra
docker compose up -d --build
docker compose ps   # confirmar que app, nginx, db, redis, queue, scheduler estén "Up"
```

La primera vez que el contenedor `app` se construya, el `Dockerfile` corre `composer install` dentro de la imagen — ahí es cuando realmente se descargan `laravel/framework`, `inertiajs/inertia-laravel`, etc. (nunca ejecuté `composer install` yo mismo: no tengo PHP/Composer disponible ni acceso a packagist.org desde este entorno, así que el `composer.json` está escrito a mano y esta es la primera vez que se resuelve de verdad).

Para el frontend (Vue/Inertia/Tailwind), además:
```bash
docker compose exec app sh -c "npm install && npm run build"
```

## 5. Generar APP_KEY y correr migraciones

```bash
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
docker compose exec app php artisan db:seed --class=Database\\Seeders\\RolesAndPermissionsSeeder
docker compose exec app php artisan db:seed --class=Database\\Seeders\\CajonDelMaipoDestinationSeeder
```

## 6. Verificar PostGIS

```bash
docker compose exec db psql -U ruta360 -d ruta360 -c "SELECT extname FROM pg_extension WHERE extname = 'postgis';"
docker compose exec db psql -U ruta360 -d ruta360 -c "SELECT slug FROM destinations;"
```
Debe devolver `postgis` y `cajon-del-maipo` respectivamente.

## 7. HTTPS con Let's Encrypt (después de confirmar que el sitio responde por HTTP)

```bash
sudo apt install certbot python3-certbot-nginx -y
# si preferís mantener todo en Docker en vez de instalar certbot en el host,
# avisame y armamos el contenedor certbot dedicado en el compose
sudo certbot --nginx -d rm360.0km.app
```

## 8. Verificación final

- Abrir `http://rm360.0km.app` (o `https://` post-certbot) y confirmar que responde (aunque sea la página default de Laravel, sin frontend todavía).
- `docker compose logs app --tail=50` sin errores fatales.
- `docker compose exec app php artisan migrate:status` — todas las migraciones en estado "Ran".

## Qué falta después de esto

Con esto el VPS queda con el sitio corriendo (aunque sea la página placeholder de Home). Los próximos pasos de fase, según el roadmap:
- Fase 5: Controllers, API Resources y los endpoints reales de `/api/v1`.
- Fase 6: Policies por modelo (los stubs de `EnsureUserHasRole` y las Policies previstas en la Fase 1 README).
- Fase 7-9: Administración, dashboard del emprendedor, mapa (MapLibre GL JS todavía no está integrado en el frontend — solo Vue/Inertia/Tailwind base).
