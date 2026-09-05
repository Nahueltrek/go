# deploy-sprint6.ps1
# Corre este script parado en C:\gochile-code con:  .\deploy-sprint6.ps1
# Te va a pedir la contraseña SSH varias veces (una por archivo) — es normal.

$ErrorActionPreference = "Stop"
$server = "u451636252@147.79.125.16"
$port = "65002"
$base = "domains/go.0km.app_new"

Write-Host "== Creando carpeta Layouts en el servidor si no existe ==" -ForegroundColor Cyan
ssh -p $port $server "mkdir -p ~/$base/resources/js/Layouts"

Write-Host "== Subiendo archivos de codigo ==" -ForegroundColor Cyan
$files = @(
    "resources\js\Layouts\AdminLayout.vue",
    "resources\js\Pages\Admin\Organizaciones.vue",
    "resources\js\Pages\Admin\OrganizacionesEdit.vue",
    "resources\js\Pages\Admin\Experiencias\Index.vue",
    "resources\js\Pages\Admin\Experiencias\Form.vue",
    "resources\js\Pages\Admin\Dashboard.vue",
    "resources\js\Pages\Admin\Colaboradores.vue",
    "resources\js\Pages\Admin\Businesses.vue",
    "resources\js\Pages\Admin\Claims.vue",
    "resources\js\Pages\Admin\Reviews.vue",
    "resources\js\Pages\Admin\Articles\Index.vue",
    "resources\js\Pages\Admin\Articles\Edit.vue"
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

Write-Host "== Reemplazando carpetas build/, limpiando cache, en el servidor ==" -ForegroundColor Cyan
$remoteCommands = @"
cd ~/domains/go.0km.app_new/public
mv build build_viejo_backup6
mv build_nuevo build
cd ~/domains/go.0km.app/public_html
mv build build_viejo_backup6
mv build_nuevo build
cd ~/domains/go.0km.app_new
php artisan config:clear
php artisan route:clear
php artisan view:clear
"@

ssh -p $port $server $remoteCommands

Write-Host "== LISTO. No te olvides de purgar LiteSpeed Cache manualmente en hPanel. ==" -ForegroundColor Green
