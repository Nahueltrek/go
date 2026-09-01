# Deploy en hosting compartido de Hostinger — rm360.0km.app

Este es el instructivo real para el entorno que confirmamos por SSH: hosting
compartido (no VPS), PHP 8.3.30 vía `/opt/alt/php83`, Composer 2.9.8, MariaDB
11.8, sin Docker, sin Redis, sin acceso root/sudo.

`infra/docker-compose.yml`, `infra/Dockerfile` y `infra/VPS_SETUP.md` quedan
en el repo por si en el futuro el proyecto se muda a un VPS real — **no
aplican a este hosting**, no los ejecutes acá.

## 0. Dónde está todo ahora mismo

Confirmado por SSH: el proyecto quedó descomprimido directo en
`/home/u451636252/domains/rm360.0km.app/` (con `composer.json`, `app/`,
`artisan`, etc. en la raíz), y `public_html/` está al mismo nivel, como
carpeta hermana. Esa es, de hecho, la estructura correcta para el truco de
despliegue en hosting compartido — no hace falta reorganizar nada.

## 1. Crear la base de datos en hPanel

hPanel → Bases de datos → MySQL Databases → crear una base (ej. `u451636252_ruta360`)
y un usuario con todos los privilegios sobre ella. Hostinger antepone el
prefijo de la cuenta al nombre, así que el nombre final va a ser algo como
`u451636252_ruta360`, no `ruta360` a secas — anotá el nombre y usuario reales
que te asigne el panel.

## 2. Instalar dependencias

```bash
cd /home/u451636252/domains/rm360.0km.app
composer install --no-dev --optimize-autoloader
```

Para el frontend, si el hosting tiene Node/npm disponible:
```bash
npm install && npm run build
```
Si no hay Node en este hosting (es común que no lo haya en shared hosting),
`npm run build` se corre en tu máquina local y subís la carpeta `public/build`
generada por SFTP.

## 3. Configurar .env

```bash
cp .env.example .env
nano .env
```
Completar con los datos reales de la base creada en el paso 1:
```
APP_URL=https://rm360.0km.app
DB_DATABASE=u451636252_ruta360
DB_USERNAME=u451636252_...
DB_PASSWORD=...
```

```bash
php artisan key:generate
```

## 4. Migrar y sembrar datos

```bash
php artisan migrate --force
php artisan db:seed --class="Database\Seeders\RolesAndPermissionsSeeder" --force
php artisan db:seed --class="Database\Seeders\CajonDelMaipoDestinationSeeder" --force
```

Verificación:
```bash
php artisan migrate:status
mysql -u u451636252_... -p -e "SELECT slug FROM u451636252_ruta360.destinations;"
```
Debe listar `cajon-del-maipo`.

## 5. Publicar el frontend en public_html

Como `public_html` es fija y no se puede cambiar el document root, hay que
copiar ahí el contenido de la carpeta `public/` de Laravel (no moverla, para
no romper las rutas relativas del propio Laravel):

```bash
cp -r public/* public_html/
cp public/.htaccess public_html/ 2>/dev/null
```

**Importante:** el `index.php` de Laravel usa rutas relativas
(`__DIR__.'/../vendor/autoload.php'`, `__DIR__.'/../bootstrap/app.php'`).
Como `public_html/` está al mismo nivel que `vendor/` y `bootstrap/` (no un
nivel más abajo, como esperaría un `public/` normal), **no hace falta editar
esas rutas** — coinciden por la estructura que ya tenés. Confirmalo con:
```bash
cat public_html/index.php | grep "require"
```
Debe decir `__DIR__.'/../vendor/autoload.php'`. Si en algún momento algo no
carga, ese es el primer lugar a revisar.

## 6. Symlink de storage (manual, no con artisan storage:link)

`php artisan storage:link` crea el symlink dentro de `public/`, pero nosotros
servimos desde `public_html/`, así que se hace a mano:
```bash
ln -s ../storage/app/public public_html/storage
```

## 7. Cron jobs (reemplaza al scheduler/queue worker de Docker)

hPanel → Avanzado → Cron Jobs. Agregar dos:

**Scheduler de Laravel (cada minuto):**
```
* * * * * cd /home/u451636252/domains/rm360.0km.app && /opt/alt/php83/usr/bin/php artisan schedule:run >> /dev/null 2>&1
```

**Procesar la cola de jobs (cada 5 minutos, ya que no hay un worker persistente):**
```
*/5 * * * * cd /home/u451636252/domains/rm360.0km.app && /opt/alt/php83/usr/bin/php artisan queue:work --stop-when-empty --max-time=280 >> /dev/null 2>&1
```
`--stop-when-empty` es clave: sin eso, el comando queda corriendo indefinidamente
y en hosting compartido eso puede matarlo el propio panel o agotar tu cuota de
procesos.

## 8. Verificación final

- Abrir `https://rm360.0km.app` — debería mostrar la página placeholder ("En
  construcción — Fase 1").
- `tail -50 storage/logs/laravel.log` sin errores fatales.
- Confirmar HTTPS: hPanel normalmente emite el certificado SSL automático
  (Let's Encrypt) por dominio, sin pasos manuales de certbot.

## Qué falta después de esto

- Confirmar si el hosting tiene Node/npm para build de frontend, o si ese paso
  queda siempre como "build local + subida manual".
- Fase 5-9 del roadmap: Controllers, API, Policies, Admin, Dashboard, Mapa —
  ninguna de estas depende de si el hosting es compartido o VPS, así que el
  roadmap original sigue vigente desde acá en adelante.
