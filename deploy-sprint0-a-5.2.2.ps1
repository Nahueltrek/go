# deploy-sprint0-a-5.2.2.ps1
# Corre este script parado en C:\gochile-code con:  .\deploy-sprint0-a-5.2.2.ps1
# Te va a pedir la contrasena SSH varias veces (una por archivo) - es normal.
#
# IMPORTANTE - LEER ANTES DE CORRER:
# 1. Este script sube TODO el trabajo de Sprint 0 hasta Fase 5.2.2 de una vez
#    (dashboard, flash, SEO, ownership, moderacion, PublicLayout/Footer, Home
#    nueva). Es un cambio grande - no lo corras sin haber leido el informe
#    completo de cada fase.
# 2. HACE UN BACKUP DE LA BASE DE DATOS ANTES DE CORRER ESTO. El script corre
#    5 migraciones nuevas (una crea una columna, tres amplian ENUMs, ninguna
#    borra nada, pero backupea igual antes de tocar produccion). Desde hPanel:
#    Bases de datos > phpMyAdmin > Exportar. Nadie mas que vos puede hacer
#    este backup - Claude no tiene acceso a la base de datos real.
# 3. El build (public\build) tiene que estar generado ANTES de correr esto:
#    ejecuta `npm run build` en C:\gochile-code primero si no lo hiciste ya.
# 4. Este script NO toca tu .env de produccion - no hace falta ningun cambio
#    ahi para este deploy (VITE_APP_NAME ya quedo compilado adentro de
#    public/build en el build local, no se lee en produccion).

$ErrorActionPreference = "Stop"
$server = "u451636252@147.79.125.16"
$port = "65002"
$base = "domains/go.0km.app_new"

Write-Host "== Creando carpetas nuevas en el servidor si no existen ==" -ForegroundColor Cyan
$mkdirCommand = @"
mkdir -p ~/$base/app/Http/Controllers/Owner
mkdir -p ~/$base/resources/js/Components/Public
mkdir -p ~/$base/resources/js/Pages/Admin/Experiencias
mkdir -p ~/$base/resources/js/Pages/Admin/Proyectos
mkdir -p ~/$base/resources/js/Pages/Owner/Experiencias
mkdir -p ~/$base/resources/js/Pages/Owner/Organization
mkdir -p ~/$base/resources/js/Pages/Owner/Proyectos
"@
ssh -p $port $server $mkdirCommand

Write-Host "== Subiendo archivos PHP (controllers, models, policies, resources, config, seeders, rutas) ==" -ForegroundColor Cyan
$phpFiles = @(
    "app\Http\Controllers\Admin\OrganizationController.php",
    "app\Http\Controllers\Admin\ExperienceController.php",
    "app\Http\Controllers\Admin\ModerationController.php",
    "app\Http\Controllers\Admin\ProjectController.php",
    "app\Http\Controllers\Owner\ExperienceController.php",
    "app\Http\Controllers\Owner\OrganizationController.php",
    "app\Http\Controllers\Owner\ProjectController.php",
    "app\Http\Controllers\CollaborationController.php",
    "app\Http\Controllers\ExperienceController.php",
    "app\Http\Controllers\HomeController.php",
    "app\Http\Controllers\OrganizationController.php",
    "app\Http\Controllers\ProjectController.php",
    "app\Http\Middleware\HandleInertiaRequests.php",
    "app\Http\Resources\ExperienceResource.php",
    "app\Http\Resources\OrganizationResource.php",
    "app\Http\Resources\ProjectResource.php",
    "app\Models\Business.php",
    "app\Models\CollaborationRequest.php",
    "app\Models\Experience.php",
    "app\Models\Organization.php",
    "app\Models\Project.php",
    "app\Models\User.php",
    "app\Policies\ExperiencePolicy.php",
    "app\Policies\OrganizationPolicy.php",
    "app\Policies\ProjectPolicy.php",
    "config\app.php",
    "config\mail.php",
    "database\seeders\CreateAdminSeeder.php",
    "routes\web.php",
    "database\migrations\2026_09_05_000001_add_cover_image_to_experiences.php",
    "database\migrations\2026_09_05_000002_add_business_fields_to_organizations.php",
    "database\migrations\2026_09_05_000003_add_user_id_to_collaboration_requests.php",
    "database\migrations\2026_09_05_000004_add_moderation_states_to_experiences_status.php",
    "database\migrations\2026_09_05_000005_add_moderation_states_to_projects_status.php"
)
foreach ($f in $phpFiles) {
    $remotePath = $f -replace '\\', '/'
    Write-Host "Subiendo $f ..." -ForegroundColor Yellow
    scp -P $port "$f" "${server}:${base}/${remotePath}"
}

Write-Host "== Subiendo archivos de frontend (Vue/CSS/JS/Blade) ==" -ForegroundColor Cyan
$frontFiles = @(
    "resources\css\app.css",
    "resources\js\app.js",
    "resources\views\app.blade.php",
    "resources\js\Layouts\AdminLayout.vue",
    "resources\js\Layouts\OwnerLayout.vue",
    "resources\js\Layouts\PublicLayout.vue",
    "resources\js\Components\Footer.vue",
    "resources\js\Components\SeoHead.vue",
    "resources\js\Components\Public\CategoryChips.vue",
    "resources\js\Components\Public\ContactButtons.vue",
    "resources\js\Components\Public\EntityHero.vue",
    "resources\js\Components\Public\LocationMap.vue",
    "resources\js\Pages\Admin\Articles\Edit.vue",
    "resources\js\Pages\Admin\Articles\Index.vue",
    "resources\js\Pages\Admin\Businesses.vue",
    "resources\js\Pages\Admin\Claims.vue",
    "resources\js\Pages\Admin\Colaboradores.vue",
    "resources\js\Pages\Admin\Dashboard.vue",
    "resources\js\Pages\Admin\Organizaciones.vue",
    "resources\js\Pages\Admin\OrganizacionesEdit.vue",
    "resources\js\Pages\Admin\Reviews.vue",
    "resources\js\Pages\Admin\Moderacion.vue",
    "resources\js\Pages\Admin\Experiencias\Form.vue",
    "resources\js\Pages\Admin\Experiencias\Index.vue",
    "resources\js\Pages\Admin\Proyectos\Form.vue",
    "resources\js\Pages\Admin\Proyectos\Index.vue",
    "resources\js\Pages\Owner\Experiencias\Form.vue",
    "resources\js\Pages\Owner\Experiencias\Index.vue",
    "resources\js\Pages\Owner\Organization\Edit.vue",
    "resources\js\Pages\Owner\Organization\Show.vue",
    "resources\js\Pages\Owner\Proyectos\Form.vue",
    "resources\js\Pages\Owner\Proyectos\Index.vue",
    "resources\js\Pages\Public\Agenda.vue",
    "resources\js\Pages\Public\Bitacora.vue",
    "resources\js\Pages\Public\Experiencia.vue",
    "resources\js\Pages\Public\Explorar.vue",
    "resources\js\Pages\Public\Home.vue",
    "resources\js\Pages\Public\Mapa.vue",
    "resources\js\Pages\Public\Operador.vue",
    "resources\js\Pages\Public\Proyecto.vue"
)
foreach ($f in $frontFiles) {
    $remotePath = $f -replace '\\', '/'
    Write-Host "Subiendo $f ..." -ForegroundColor Yellow
    scp -P $port "$f" "${server}:${base}/${remotePath}"
}

Write-Host "== Subiendo carpeta build/ a go.0km.app_new ==" -ForegroundColor Cyan
scp -P $port -r "public\build" "${server}:${base}/public/build_nuevo"

Write-Host "== Subiendo carpeta build/ a public_html ==" -ForegroundColor Cyan
scp -P $port -r "public\build" "${server}:domains/go.0km.app/public_html/build_nuevo"

Write-Host "== Reemplazando carpetas build/, migrando, limpiando cache ==" -ForegroundColor Cyan
Write-Host "   (esto corre 'php artisan migrate --force' - confirma que ya hiciste el backup)" -ForegroundColor Red
$remoteCommands = @"
cd ~/domains/go.0km.app_new/public
mv build build_viejo_backup_pre_5_2_2
mv build_nuevo build
cd ~/domains/go.0km.app/public_html
mv build build_viejo_backup_pre_5_2_2
mv build_nuevo build
cd ~/domains/go.0km.app_new
php artisan migrate --force
php artisan config:clear
php artisan route:clear
php artisan view:clear
"@
ssh -p $port $server $remoteCommands

Write-Host "== LISTO. No te olvides de purgar LiteSpeed Cache manualmente en hPanel. ==" -ForegroundColor Green
Write-Host "== Despues revisa manualmente: /, /explorar, /mapa, /agenda, /bitacora, /admin, /mi-organizacion ==" -ForegroundColor Green
