# deploy-sprint7.ps1
# Corre este script parado en C:\gochile-code con:  .\deploy-sprint7.ps1
# Te va a pedir la contraseña SSH varias veces — es normal.

$ErrorActionPreference = "Stop"
$server = "u451636252@147.79.125.16"
$port = "65002"
$base = "domains/go.0km.app_new"

Write-Host "== Subiendo archivos de codigo ==" -ForegroundColor Cyan
$files = @(
    "database\migrations\2026_09_05_000002_add_business_fields_to_organizations.php",
    "app\Models\Organization.php",
    "app\Http\Resources\OrganizationResource.php",
    "app\Http\Controllers\Admin\OrganizationController.php",
    "resources\js\Pages\Admin\OrganizacionesEdit.vue",
    "app\Models\Business.php"
)

foreach ($f in $files) {
    $remotePath = $f -replace '\\', '/'
    Write-Host "Subiendo $f ..." -ForegroundColor Yellow
    scp -P $port "$f" "${server}:${base}/${remotePath}"
}

Write-Host "== Subiendo carpeta build/ a go.0km.app_new ==" -ForegroundColor Cyan
scp -P $port -r "public\build" "${server}:${base}/public/build_nuevo"

Write-Host "== Subiendo carpeta build/ a public_html ==" -ForegroundColor Cyan
scp -P $port -r "public\build" "${server}:domains/go.0km.app/public_html/build_nuevo"

Write-Host "== Reemplazando carpetas build/, migrando, limpiando cache ==" -ForegroundColor Cyan
$remoteCommands = @"
cd ~/domains/go.0km.app_new/public
mv build build_viejo_backup7
mv build_nuevo build
cd ~/domains/go.0km.app/public_html
mv build build_viejo_backup7
mv build_nuevo build
cd ~/domains/go.0km.app_new
php artisan migrate --force
php artisan config:clear
php artisan route:clear
php artisan view:clear
"@

ssh -p $port $server $remoteCommands

Write-Host "== LISTO. No te olvides de purgar LiteSpeed Cache manualmente en hPanel. ==" -ForegroundColor Green
